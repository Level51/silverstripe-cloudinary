<?php

use \Cloudinary\Uploader as API;

/**
 * Class CloudinaryService
 */
class CloudinaryService {

    private static $instance = null;

    private function __construct() {

        // Set Cloudinary api credentials
        \Cloudinary::config(array(
            "cloud_name" => Config::inst()->get('Cloudinary', 'cloud_name'),
            "api_key"    => Config::inst()->get('Cloudinary', 'api_key'),
            "api_secret" => Config::inst()->get('Cloudinary', 'api_secret')
        ));
    }

    private function __clone() { }

    public static function inst() {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Deletes the resources identified by publicID.
     *
     * @param string $publicID CL public_id of the resource
     *
     * @return mixed
     *
     * @see https://cloudinary.com/documentation/admin_api#delete_all_or_selected_resources
     */
    public function destroy($publicID) {
        return API::destroy($publicID, [
                'invalidate' => true,
                'type'       => Config::inst()->get('Cloudinary', 'image_type')
            ]
        );
    }

    /**
     * Generates an signature for uploads to CL backend.
     *
     * @param array $paramsToSign
     *
     * @return string
     *
     * @see https://cloudinary.com/documentation/upload_images#generating_authentication_signatures
     */
    public function sign($paramsToSign) {
        return Cloudinary::api_sign_request(
            $paramsToSign,
            Config::inst()->get('Cloudinary', 'api_secret')
        );
    }

    /**
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
        return Cloudinary::private_download_url($publicID, $format, [
            'expires_at' => $expires,
            'attachment' => $asDownload
        ]);
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
        return Cloudinary::cloudinary_url($publicID, $options);
    }
}
