<?php

/**
 * Controller for Cloudinary upload/image specific admin actions.
 *
 * Listens on the /admin/cloudinary route and handles the creation of
 * CloudinaryImage objects and also delete actions.
 */
class CloudinaryUploadController extends Controller {

    private static $allowed_actions = ['onAfterUpload', 'onAfterMultipleUpload', 'deleteImage', 'deleteImages', 'generateSignature'];

    private static $url_handlers = [
        'onAfterUpload'         => 'onAfterUpload',
        'onAfterMultipleUpload' => 'onAfterMultipleUpload',
        'deleteImage'           => 'deleteImage',
        'deleteImages'          => 'deleteImages',
        'generateSignature'     => 'generateSignature'
    ];

    public function index() {
        return $this->httpError(403);
    }

    /**
     * Create a single CloudinaryImage object from the request vars.
     *
     * @param array $vars Response array from the cloudinary upload
     *
     * @return CloudinaryImage
     */
    private function createImageObject($vars) {
        $image = new CloudinaryImage();
        $image->PublicID = $vars['public_id'];
        $image->Version = $vars['version'];
        $image->Format = $vars['format'];
        $image->eTag = $vars['etag'];
        $image->URL = $vars['secure_url'];
        $image->Filename = $vars['original_filename'];
        $image->ThumbnailURL = isset($vars['thumbnail']) ? $vars['thumbnail'] : null;

        $this->extend('onBeforeImageCreated', $image, $vars);

        try {
            $image->write();
        } catch (Exception $e) {
            SS_Log::log($e->getMessage(), SS_Log::ERR);
        }

        $this->extend('onAfterImageCreated', $image, $vars);

        return $image;
    }

    /**
     * Handle single file upload.
     *
     * Creates a CloudinaryImage and returns the ID.
     *
     * @return int The ID of the newly created image object, linked to the actual object as soon as this is saved.
     */
    public function onAfterUpload() {
        return $this->createImageObject($this->getRequest()->postVars())->ID;
    }

    /**
     * Handle multi file upload.
     *
     * Creates a CloudinaryImage object for each uploaded image.
     * The relation to the DataObject is also set.
     *
     * @return string
     * @throws ValidationException
     */
    public function onAfterMultipleUpload() {
        $postVars = $this->getRequest()->postVars();
        $relField = $postVars['relation']['field'];
        $relId = $postVars['relation']['id'];

        $response = '';
        foreach ($postVars['images'] as $imageVars) {
            $image = $this->createImageObject($imageVars);
            $image->$relField = $relId;
            $image->write();

            $response .= $image->renderWith('CloudinaryMultiUploadItem');
        }

        return $response;
    }

    /**
     * Delete the given CloudinaryImage object. Triggers the remote delete in the onBeforeDelete function.
     */
    public function deleteImage() {
        CloudinaryImage::get()->byID($this->getRequest()->postVar('id'))->delete();
    }

    /**
     * Delete multiple CloudinaryImage objects by their IDs.
     */
    public function deleteImages() {
        CloudinaryImage::get()->filter('ID', $this->getRequest()->postVar('ids'))->removeAll();
    }

    /**
     * @return string Generate a signature needed for signed uploads
     */
    public function generateSignature() {
        return CloudinaryService::inst()->sign($this->getRequest()->getVar('data'));
    }
}
