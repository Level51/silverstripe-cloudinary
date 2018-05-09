<?php

/**
 * Controller for Cloudinary upload/image specific admin actions.
 *
 * Listens on the /admin/cloudinary route and handles the creation of
 * CloudinaryImage objects and also delete actions.
 */
class CloudinaryUploadController extends Controller {

    private static $allowed_actions = ['onAfterUpload', 'deleteImage', 'generateSignature'];

    private static $url_handlers = [
        'onAfterUpload'     => 'onAfterUpload',
        'deleteImage'       => 'deleteImage',
        'generateSignature' => 'generateSignature'
    ];

    public function index() {
        return $this->httpError(403);
    }

    /**
     * Create a CloudinaryImage object after successful upload.
     *
     * @return int The ID of the newly created image object, linked to the actual object as soon as this is saved.
     */
    public function onAfterUpload() {
        $postVars = $this->getRequest()->postVars();

        $image = new CloudinaryImage();
        $image->PublicID = $postVars['public_id'];
        $image->Version = $postVars['version'];
        $image->Format = $postVars['format'];
        $image->eTag = $postVars['etag'];
        $image->URL = $postVars['secure_url'];
        $image->Filename = $postVars['original_filename'];
        $image->ThumbnailURL = $postVars['thumbnail'];

        $this->extend('onBeforeImageCreated', $image, $postVars);

        try {
            $image->write();
        } catch (Exception $e) {
            SS_Log::log($e->getMessage(), SS_Log::ERR);
        }

        $this->extend('onAfterImageCreated', $image, $postVars);

        return $image->ID;
    }

    /**
     * Delete the given CloudinaryImage object. Triggers the remote delete in the onBeforeDelete function.
     */
    public function deleteImage() {
        CloudinaryImage::get()->byID($this->getRequest()->postVar('id'))->delete();
    }

    /**
     * @return string Generate a signature needed for signed uploads
     */
    public function generateSignature() {
        return CloudinaryService::inst()->sign($this->getRequest()->getVar('data'));
    }
}
