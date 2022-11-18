<?php

/**
 * Controller for Cloudinary upload/image specific admin actions.
 *
 * Listens on the /admin/cloudinary route and handles the creation of
 * Image objects and also delete actions.
 */
class CloudinaryUploadController extends Controller {

    private static $allowed_actions = ['onAfterUpload', 'onAfterMultiUpload', 'deleteImage', 'deleteImages', 'generateSignature'];

    private static $url_handlers = [
        'onAfterUpload'      => 'onAfterUpload',
        'onAfterMultiUpload' => 'onAfterMultiUpload',
        'deleteImage'        => 'deleteImage',
        'deleteImages'       => 'deleteImages',
        'generateSignature'  => 'generateSignature'
    ];

    /**
     * @throws \SS_HTTPResponse_Exception
     */
    public function index() {
        return $this->httpError(403);
    }

    private function createImageObject($vars) {
        // Try to get a thumbnail, either directly from the "thumbnail_url" if set (non-private upload)
        // Or use the first eager transformation if set
        $thumbnail = null;
        if ($vars['type'] === 'upload' && isset($vars['thumbnail_url']) && $vars['thumbnail_url'])
            $thumbnail = $vars['thumbnail_url'];
        else if (isset($vars['eager']) && is_array($vars['eager']) && !empty($vars['eager']))
            $thumbnail = $vars['eager'][0]['secure_url'];

        $image = new CloudinaryImage();
        $image->PublicID = $vars['public_id'];
        $image->Version = $vars['version'];
        $image->Format = $vars['format'];
        $image->eTag = $vars['etag'];
        $image->URL = $vars['secure_url'];
        $image->Filename = $vars['original_filename'];
        $image->ThumbnailURL = $thumbnail;
        $image->Size = $vars['bytes'];
        $image->Width = $vars['width'];
        $image->Height = $vars['height'];

        if (isset($vars['asset_id']))
            $image->AssetID = $vars['asset_id'];

        $this->extend('onBeforeImageCreated', $image, $vars);

        try {
            $image->write();
        } catch (Exception $e) {
            // TODO logging
            //SS_Log::log($e->getMessage(), SS_Log::ERR);
        }

        $this->extend('onAfterImageCreated', $image, $postVars);

        return $image;
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

        $image = $this->createImageObject($postVars);

        return json_encode($image->flatten());
    }

    public function onAfterMultiUpload() {
        $body = $this->getRequest()->getBody();

        if (!$body || !($postVars = json_decode($body, true)) || !$postVars || empty($postVars))
            return false;

        $relField = $postVars['relation']['field'];
        $relId = $postVars['relation']['id'];

        $response = [];
        foreach ($postVars['files'] as $imageVars) {
            $image = $this->createImageObject($imageVars['uploadInfo']);
            $image->$relField = $relId;
            $image->write();

            $response[] = $image->flatten();
        }

        return json_encode($response);
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

        if ($image = CloudinaryImage::get()->byID($body['id'])) {
            $image->delete();

            return true;
        }

        return false;
    }

    /**
     * @return string Generate a signature needed for signed uploads
     */
    public function generateSignature() {
        return CloudinaryService::inst()->sign($this->getRequest()->getVars());
    }
}
