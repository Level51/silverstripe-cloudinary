<?php

namespace Level51\Cloudinary;

use Exception;
use SilverStripe\Control\Controller;

/**
 * Controller for Cloudinary upload/image specific admin actions.
 *
 * Listens on the /admin/cloudinary route and handles the creation of
 * Image objects and also delete actions.
 */
class UploadController extends Controller {

    private static $allowed_actions = ['onAfterUpload', 'deleteImage', 'generateSignature'];

    private static $url_handlers = [
        'onAfterUpload'     => 'onAfterUpload',
        'deleteImage'       => 'deleteImage',
        'generateSignature' => 'generateSignature'
    ];

    /**
     * @throws \SilverStripe\Control\HTTPResponse_Exception
     */
    public function index() {
        return $this->httpError(403);
    }

    /**
     * Create a CloudinaryImage object after successful upload.
     *
     * @return array|bool Flat version of the newly created image object, linked to the actual object as soon as this is saved.
     */
    public function onAfterUpload() {
        $body = $this->getRequest()->getBody();

        if (!$body || !($postVars = json_decode($body, true)) || !$postVars || empty($postVars))
            return false;

        // Try to get a thumbnail, either directly from the "thumbnail_url" if set (non-private upload)
        // Or use the first eager transformation if set
        $thumbnail = null;
        if ($postVars['type'] === 'upload' && isset($postVars['thumbnail_url']) && $postVars['thumbnail_url'])
            $thumbnail = $postVars['thumbnail_url'];
        else if (isset($postVars['eager']) && is_array($postVars['eager']) && !empty($postVars['eager']))
            $thumbnail = $postVars['eager'][0]['secure_url'];

        $image = new Image();
        $image->PublicID = $postVars['public_id'];
        $image->Version = $postVars['version'];
        $image->Format = $postVars['format'];
        $image->eTag = $postVars['etag'];
        $image->URL = $postVars['secure_url'];
        $image->Filename = $postVars['original_filename'];
        $image->ThumbnailURL = $thumbnail;
        $image->Size = $postVars['bytes'];
        $image->Width = $postVars['width'];
        $image->Height = $postVars['height'];

        $this->extend('onBeforeImageCreated', $image, $postVars);

        try {
            $image->write();
        } catch (Exception $e) {
            // TODO logging
            //SS_Log::log($e->getMessage(), SS_Log::ERR);
        }

        $this->extend('onAfterImageCreated', $image, $postVars);

        return json_encode($image->flatten());
    }

    /**
     * Delete the given CloudinaryImage object. Triggers the remote delete in the onBeforeDelete function.
     */
    public function deleteImage() {
        $body = $this->getRequest()->getBody();
        if (!$body) return false;

        $body = json_decode($body, true);

        if (!$body || empty($body) || !isset($body['id']))
            return false;

        if ($image = Image::get()->byID($body['id'])) {
            $image->delete();

            return true;
        }

        return false;
    }

    /**
     * @return string Generate a signature needed for signed uploads
     */
    public function generateSignature() {
        return Service::inst()->sign($this->getRequest()->getVars());
    }
}
