<?php

namespace Level51\Cloudinary;

use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\ApiUtils;
use Cloudinary\Asset\Image;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi as API;
use Cloudinary\Tag\ImageTag;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Config_ForClass;

/**
 * Class Service
 */
class Service {

    /**
     * @var null|Service
     */
    private static ?Service $instance = null;

    private function __construct() {

        // Set Cloudinary api credentials
        Configuration::instance()->init(array(
            "cloud_name" => self::config()->get('cloud_name'),
            "api_key"    => self::config()->get('api_key'),
            "api_secret" => self::config()->get('api_secret')
        ));
    }

    private function __clone() { }

    /**
     * @return Service|null
     */
    public static function inst(): ?Service
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @return Config_ForClass
     */
    public static function config(): Config_ForClass
    {
        return Config::forClass('Level51\Cloudinary\Cloudinary');
    }

    /**
     * Deletes the resources identified by publicID.
     *
     * @param string $publicID CL public_id of the resource
     *
     * @return ApiResponse
     *
     * @see https://cloudinary.com/documentation/admin_api#delete_all_or_selected_resources
     */
    public function destroy(string $publicID): ApiResponse
    {
        return (new API)->destroy($publicID, [
                'invalidate' => true,
                'type'       => self::config()->get('image_type')
            ]
        );
    }

    /**
     * Generates a signature for uploads to CL backend.
     *
     * @param array $paramsToSign
     *
     * @return string
     *
     * @see https://cloudinary.com/documentation/upload_images#generating_authentication_signatures
     */
    public function sign(array $paramsToSign): string
    {
        return ApiUtils::signParameters(
            $paramsToSign,
            self::config()->get('api_secret')
        );
    }

    /**
     * @deprecated
     * Generates a temporary available download link for private images.
     *
     * @param string  $publicID   CL public_id of the resource
     * @param string  $format     The image format
     * @param int     $expires    Link expiration as unix timestamp
     * @param boolean $asDownload Whether the created link should start the download immediately or not
     *
     * @return string
     */
    public function privateDownloadLink($publicID, $format, $expires, $asDownload) {
        $params = ['expires_at' => $expires];

        if ($asDownload)
            $params['attachment'] = true;

//        return Cloudinary::private_download_url($publicID, $format, $params);
    }

    /**
     * Get the Cloudinary url for the given public id including options.
     *
     * @param string $publicID
     * @param array  $options
     *
     * @return string
     */
    public function getCloudinaryUrl($publicID, $options) {
        if (!$publicID || $publicID == "")
            return "";
        return (string) Image::fromParams($publicID, $options)->toUrl();
    }
}
