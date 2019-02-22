<?php

/**
 * Upload field for cloudinary using their javascript widget.
 *
 * @see https://cloudinary.com/documentation/upload_widget#upload_widget_options for available options
 */
class CloudinaryUploadField extends FormField {

    private $folder = '';
    private $cropping = 'server';
    private $cropping_aspect_ratio = null;
    private static $use_signed = true;

    protected $fieldHolderTemplate = 'CloudinaryUploadField_holder';

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
        Requirements::javascript(SILVERSTRIPE_CLOUDINARY_DIR . '/dist/cloudinary-upload-field.js');
        Requirements::css(SILVERSTRIPE_CLOUDINARY_DIR . '/dist/cloudinary-upload-field.css');

        return parent::Field($properties);
    }

    /**
     * Return a clone with readonly flag set to true.
     *
     * @return CloudinaryUploadField|FormField
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
        return !!Config::inst()->get('Cloudinary', 'show_remove');
    }

    /**
     * Get the options passed to the FE via the data-options attribute on the container div.
     *
     * @return string
     */
    public function getOptions() {
        $options = [
            'name'                  => $this->getName(),
            'cloud_name'            => $this->getCloudName(),
            'upload_preset'         => Config::inst()->get('Cloudinary', 'upload_preset'),
            'theme'                 => Config::inst()->get('Cloudinary', 'theme'),
            'folder'                => $this->folder,
            'cropping'              => $this->cropping,
            'cropping_aspect_ratio' => $this->cropping_aspect_ratio,
            'use_signed'            => Config::inst()->get('Cloudinary', 'use_signed')
        ];

        if (self::$use_signed)
            $options['api_key'] = Config::inst()->get('Cloudinary', 'api_key');

        return Convert::array2json($options);
    }

    /**
     * Get some information about the linked file if there is any.
     *
     * @return null|ArrayData
     * @throws Exception
     */
    public function getFile() {
        if ($this->Value()) {
            if ($file = CloudinaryImage::get()->byID($this->Value()))
                return ArrayData::create([
                    'Thumbnail'        => $file->ThumbnailURL,
                    'ID'               => $file->ID,
                    'Filename'         => $file->Filename,
                    'PublicID'         => $file->PublicID,
                    'MediaLibraryLink' => $file->getMediaLibraryLink()
                ]);
        }

        return null;
    }

    /**
     * Get the upload folder/path.
     *
     * @return string
     */
    public function getFolder() {
        return $this->folder;
    }

    /**
     * Get the Cloudinary "cloud name".
     *
     * @return string
     */
    public function getCloudName() {
        return Config::inst()->get('Cloudinary', 'cloud_name');
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
