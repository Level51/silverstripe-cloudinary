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
    private static $use_signed = false;

    protected $fieldHolderTemplate = 'UploadField_holder';

    protected $extraClasses = ['cloudinaryupload'];

    public $allowedExtensions = [];

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
        Requirements::javascript(SILVERSTRIPE_CLOUDINARY_DIR . '/client/dist/level51-cloudinary-upload-field.js');
        Requirements::css(SILVERSTRIPE_CLOUDINARY_DIR . '/client/dist/level51-cloudinary-upload-field.css');

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
        return !!CloudinaryService::config()->get('show_remove');
    }

    /**
     * Get the frontend payload passed to the vue component.
     *
     * @param bool $asJSON
     *
     * @return string|array
     */
    public function getPayload($asJSON = true) {
        $payload = [
            'id'                => $this->ID(),
            'name'              => $this->getName(),
            'cloudinaryOptions' => [
                'cloudName'           => $this->getCloudName(),
                'uploadPreset'        => CloudinaryService::config()->get('upload_preset'),
                'theme'               => CloudinaryService::config()->get('theme'),
                'folder'              => $this->getFolderName(),
                'cropping'            => $this->cropping,
                'croppingAspectRatio' => $this->cropping_aspect_ratio,
                'useSigned'           => CloudinaryService::config()->get('use_signed'),
                'allowedExtensions'   => $this->getAllowedExtensions()
            ],
            'options'           => [
                'showRemove' => $this->showRemove()
            ],
            'file'              => ($file = $this->getFile()) ? $file->flatten() : null,
            'i18n'              => $this->getFrontendI18NPayload()
        ];

        if (self::$use_signed)
            $payload['cloudinaryOptions']['apiKey'] = CloudinaryService::config()->get('api_key');

        return $asJSON ? json_encode($payload) : $payload;
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
            'CTA_SHOW',
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
            'CLOUDINARY_INFO',
            'ERR_MISSING_UPLOAD_PRESET'
        ];

        foreach ($keys as $key) {
            $payload[$key] = _t('Cloudinary.' . $key, " ");
        }

        return $payload;
    }

    /**
     * Get the cloudinary image record according to the value if set.
     *
     * @return DataObject|CloudinaryImage|null
     */
    public function getFile() {
        $value = $this->Value();

        if ($value) {
            if ($value instanceof CloudinaryImage && $value->exists()) return $value;
            else if (is_int($value) || is_string($value)) return CloudinaryImage::get()->byID($value);
        }

        return null;
    }

    /**
     * Get the upload folder/path.
     *
     * @return string
     *
     * @deprecated 1.3.0 use getFolderName instead
     */
    public function getFolder() {
        return $this->getFolderName();
    }

    /**
     * Get the upload folder/path.
     *
     * @return string
     */
    public function getFolderName() {
        $root = CloudinaryService::config()->get('root_folder');

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
        return CloudinaryService::config()->get('cloud_name');
    }

    /**
     * Get allowed file extensions.
     *
     * @return array|null
     */
    public function getAllowedExtensions() {
        // Check limitations per field instance
        if ($this->allowedExtensions && is_array($this->allowedExtensions) && count($this->allowedExtensions) > 0) {
            return $this->allowedExtensions;
        }

        // Check for global setting via config API
        if (($globalSetting = CloudinaryService::config()->get('allowed_extensions')) &&
            is_array($globalSetting) &&
            count($globalSetting) > 0) {
            return $globalSetting;
        }

        return null;
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
     * @deprecated 1.3.0 use setFolderName instead
     *
     */
    public function setFolder($folder) {
        return $this->setFolderName($folder);
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
     */
    public function setFolderName($folder) {
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

    /**
     * Set allowed file extensions.
     *
     * @param array $rules
     *
     * @return CloudinaryUploadField|void
     */
    public function setAllowedExtensions($rules) {
        if (!is_array($rules)) {
            return $this;
        }

        // make sure all rules are lowercase
        foreach ($rules as &$rule) {
            $rule = strtolower($rule);
        }

        $this->allowedExtensions = $rules;

        return $this;
    }

    /**
     * Set allowed file extensions by category.
     *
     * Allowed categories are "image" or "image/supported", see File::$app_categories for included extensions.
     *
     * @param string $category
     *
     * @return CloudinaryUploadField
     *
     * @throws Exception
     */
    public function setAllowedFileCategories($category) {
        if (!is_string($category) || $category !== 'image')
            throw new Exception(_t('Cloudinary.ERR_INVALID_FILE_CATEGORY'));

        $extensions = \Config::inst()->get(File::class, 'app_categories')[$category];

        return $this->setAllowedExtensions($extensions);
    }
}
