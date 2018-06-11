<?php

/**
 * Base class for the single and multi Cloudinary upload field.
 */
class CloudinaryUploadFormField extends FormField {

    /**
     * Destination folder name/path.
     *
     * Please note that the actual folder creation works only if the related settings is
     * made within the Cloudinary admin console.
     *
     * @var string
     */
    private $folder = '';

    /**
     * @var bool Whether to use signed uploads or not
     */
    private static $use_signed = true;

    /**
     * Get the options passed to the FE via the data-options attribute on the container div.
     *
     * @return array
     */
    public function getOptions() {
        $options = [
            'name'          => $this->getName(),
            'cloud_name'    => $this->getCloudName(),
            'upload_preset' => Config::inst()->get('Cloudinary', 'upload_preset'),
            'theme'         => Config::inst()->get('Cloudinary', 'theme'),
            'folder'        => $this->folder,
            'use_signed'    => Config::inst()->get('Cloudinary', 'use_signed')
        ];

        if (self::$use_signed)
            $options['api_key'] = Config::inst()->get('Cloudinary', 'api_key');

        return $options;
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
}
