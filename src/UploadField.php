<?php

namespace Level51\Cloudinary;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FormField;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;

/**
 * Upload field for cloudinary using their javascript widget.
 *
 * @see https://cloudinary.com/documentation/upload_widget#upload_widget_options for available options
 */
class UploadField extends FormField {

    private $folder = '';
    private $cropping = 'server';
    private $cropping_aspect_ratio = null;
    private static $use_signed = true;

    protected $fieldHolderTemplate = 'UploadField_holder';

    protected $extraClasses = ['cloudinaryupload'];

    /**
     * Get the actual upload field.
     *
     * Basically just calls the parent method and requires some assets.
     *
     * @param array $properties
     *
     * @return string
     */
    public function Field($properties = array()) {
        Requirements::javascript('level51/silverstripe-cloudinary: client/dist/level51-cloudinary-upload-field.js');
        Requirements::css('level51/silverstripe-cloudinary: client/dist/level51-cloudinary-upload-field.css');

        return parent::Field($properties);
    }

    /**
     * Return a clone with readonly flag set to true.
     *
     * @return UploadField|FormField
     */
    public function performReadonlyTransformation() {
        $clone = clone $this;
        $clone->setReadonly(true);

        return $clone;
    }

    /**
     * Check if the remove button should be shown.
     *
     * @return boolean
     */
    public function showRemove() {
        return !!Service::config()->get('show_remove');
    }

    /**
     * Get the frontend payload passed to the vue component.
     *
     * @return string
     */
    public function getPayload() {
        $payload = [
            'id'                => $this->ID(),
            'name'              => $this->getName(),
            'cloudinaryOptions' => [
                'cloudName'           => $this->getCloudName(),
                'uploadPreset'        => Service::config()->get('upload_preset'),
                'theme'               => Service::config()->get('theme'),
                'folder'              => $this->getFolder(),
                'cropping'            => $this->cropping,
                'croppingAspectRatio' => $this->cropping_aspect_ratio,
                'useSigned'           => Service::config()->get('use_signed'),
            ],
            'options'           => [
                'showRemove' => $this->showRemove()
            ],
            'file'              => ($file = $this->getFile()) ? $file->flatten() : null,
            'i18n'              => $this->getFrontendI18NPayload()
        ];

        if (self::$use_signed)
            $payload['cloudinaryOptions']['apiKey'] = Service::config()->get('api_key');

        return json_encode($payload);
    }

    /**
     * Prepare labels for the vue component.
     *
     * @return array
     */
    private function getFrontendI18NPayload() {
        $payload = [];
        $keys = [
            'CTA_DELETE',
            'CTA_UPLOAD',
            'CTA_UPLOAD_REPLACE',
            'CTA_REMOVE',
            'FILENAME',
            'PUBLIC_ID',
            'CLOUD_NAME',
            'DESTINATION_FOLDER',
            'SIZE',
            'WIDTH',
            'HEIGHT',
            'FORMAT',
            'CLOUDINARY_INFO'
        ];

        foreach ($keys as $key) {
            $payload[$key] = _t('Level51\Cloudinary\Cloudinary.' . $key);
        }

        return $payload;
    }

    /**
     * Get the cloudinary image record according to the value if set.
     *
     * @return \SilverStripe\ORM\DataObject|Image|null
     */
    public function getFile() {
        if ($this->Value())
            return Image::get()->byID($this->Value());

        return null;
    }

    /**
     * Get the upload folder/path.
     *
     * @return string
     */
    public function getFolder() {
        $root = Service::config()->get('root_folder');

        if (!$this->folder)
            return $root;

        return Controller::join_links($root, $this->folder);
    }

    /**
     * Get the Cloudinary "cloud name".
     *
     * @return string
     */
    public function getCloudName() {
        return Service::config()->get('cloud_name');
    }

    /**
     * Change the folder to save into.
     *
     * Ensure to set the "Auto-create folders" options in https://cloudinary.com/console/settings/upload
     * so folders are actually created.
     *
     * @param string $folder The folder or path
     *
     * @return $this
     *
     */
    public function setFolder($folder) {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Disable the cropping interface before the actual upload.
     *
     * @return $this
     */
    public function disableCropping() {
        $this->cropping = null;

        return $this;
    }

    /**
     * Set a aspect ratio for the image cropping.
     *
     * Only relevant if cropping is enabled.
     *
     * @param float $ratio E.g. 16/9
     *
     * @return $this
     */
    public function setRatio($ratio) {
        $this->cropping_aspect_ratio = $ratio;

        return $this;
    }
}
