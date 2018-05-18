<?php

/**
 * DataObject storing relevant information for a cloudinary image file representation.
 *
 * @property string $PublicID
 * @property string $Version
 * @property string $Format
 * @property string $eTag
 * @property string $URL
 * @property string $Filename
 * @property string $ThumbnailURL
 */
class CloudinaryImage extends DataObject {

    private $transformOptions = null;

    private static $db = [
        'PublicID'     => 'Varchar(100)',
        'Version'      => 'Varchar',
        'Format'       => 'Varchar(5)',
        'eTag'         => 'Varchar(100)',
        'URL'          => 'Varchar(255)',
        'Filename'     => 'Varchar(255)',
        'ThumbnailURL' => 'Varchar(255)'
    ];

    public function onBeforeDelete() {
        parent::onBeforeDelete();

        // Delete remote files
        CloudinaryService::inst()->destroy($this->PublicID);
    }

    public function Link() {

        // Check for transformation options (set through methods like "Fill")
        $options = $this->transformOptions ?: [];

        // Auto choose file format, uses e.g. webP for supported browsers, see http://cloudinary.com/documentation/image_transformations#automatic_format_selection
        $options['fetch_format'] = 'auto';

        // Let Cloudinary choose the quality, see http://cloudinary.com/documentation/image_transformations#automatic_quality_and_encoding_settings
        $options['quality'] = 'auto';

        // Deliver jpg instead of png if the image has no transparency, see point 4 here https://support.cloudinary.com/hc/en-us/articles/202521522-How-can-I-make-my-images-load-faster-
        $options['flags'] = 'lossy';

        // Always use secure https urls
        $options['secure'] = true;

        return CloudinaryService::inst()->getCloudinaryUrl($this->PublicID, $options);
    }

    public function forTemplate() {
        return $this->Link();
    }

    public function Fill($width, $height) {
        $this->transformOptions = [
            'crop'   => 'fill',
            'width'  => $width,
            'height' => $height
        ];

        return $this;
    }

    public function Fit($width, $height) {
        $this->transformOptions = [
            'crop'   => 'fit',
            'width'  => $width,
            'height' => $height
        ];

        return $this;
    }

    public function Pad($width, $height) {
        $this->transformOptions = [
            'crop'   => 'pad',
            'width'  => $width,
            'height' => $height
        ];

        return $this;
    }

    public function ScaleWidth($width) {
        $this->transformOptions = [
            'width' => $width
        ];

        return $this;
    }

    public function ScaleHeight($height) {
        $this->transformOptions = [
            'height' => $height
        ];

        return $this;
    }

    public function getTag() {
        return '<img src="' . $this->Link() . '" alt="' . $this->Filename . '" />';
    }

    /**
     * @param int  $expires How long the URL should be valid as unix timestamp
     *
     * @param bool $asDownload
     *
     * @return string
     * @throws Exception
     */
    public function getTemporaryDownloadLink($expires = null, $asDownload = true) {

        // Break if public id unknown
        if (!$this->PublicID)
            throw new Exception(_t('Cloudinary.ERR_DOWNLOAD_NO_PUBLIC_ID', null, null, [
                'ImageID' => $this->ID
            ]));

        if (!$expires)
            $expires = \Carbon\Carbon::now()->addHour()->format('U');

        return CloudinaryService::inst()->privateDownloadLink($this->PublicID, $this->Format, $expires, $asDownload);
    }
}
