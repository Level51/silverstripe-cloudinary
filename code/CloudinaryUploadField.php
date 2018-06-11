<?php

/**
 * Upload field for cloudinary using their javascript widget.
 *
 * @see https://cloudinary.com/documentation/upload_widget#upload_widget_options for available options
 */
class CloudinaryUploadField extends CloudinaryUploadFormField {

    private $cropping = 'server';
    private $cropping_aspect_ratio = null;

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
        $options = parent::getOptions();

        $options = array_merge($options, [
            'cropping'              => $this->cropping,
            'cropping_aspect_ratio' => $this->cropping_aspect_ratio,
        ]);

        return Convert::array2json($options);
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
