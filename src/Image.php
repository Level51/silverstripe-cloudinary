<?php

namespace Level51\Cloudinary;

use Carbon\Carbon;
use Exception;
use SilverStripe\Assets\File;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataObject;

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
 * @property int    $Size  File size in bytes
 * @property int    Width  Image width after upload, so with incoming transformation applied
 * @property int    Height Image height after upload, so with incoming transformation applied
 */
class Image extends DataObject {

    private $transformOptions = null;
    private $effectOptions = null;

    private static $artistic_filters = [
        'al_dente', 'athena', 'audrey', 'aurora', 'daguerre', 'eucalyptus', 'fes', 'frost', 'hairspray', 'hokusai',
        'incognito', 'linen', 'peacock', 'primavera', 'quartz', 'red_rock', 'refresh', 'sizzle', 'sonnet', 'ukulele', 'zorro'
    ];

    private static $table_name = 'CloudinaryImage';

    private static $db = [
        'PublicID'     => 'Varchar(100)',
        'Version'      => 'Varchar',
        'Format'       => 'Varchar(5)',
        'eTag'         => 'Varchar(100)',
        'URL'          => 'Varchar(255)',
        'Filename'     => 'Varchar(255)',
        'ThumbnailURL' => 'Varchar(255)',
        'Size'         => 'Int',
        'Width'        => 'Int',
        'Height'       => 'Int'
    ];

    public function onBeforeDelete() {
        parent::onBeforeDelete();

        // Delete remote files if there is a public id (not the case for mock images, which are actually not stored at Cloudinary)
        if ($this->PublicID)
            Service::inst()->destroy($this->PublicID);
    }

    /**
     * @return string Cloudinary image link including options, transformations etc.
     */
    public function Link() {

        // Check for transformation options (set through methods like "Fill")
        $options = $this->transformOptions ?: [];

        // Check for additional effect options
        if ($this->effectOptions)
            $options = array_merge($options, $this->effectOptions);

        // Auto choose file format, uses e.g. webP for supported browsers, see http://cloudinary.com/documentation/image_transformations#automatic_format_selection
        $options['fetch_format'] = 'auto';

        // Let Cloudinary choose the quality, see http://cloudinary.com/documentation/image_transformations#automatic_quality_and_encoding_settings
        $options['quality'] = 'auto';

        // Deliver jpg instead of png if the image has no transparency, see point 4 here https://support.cloudinary.com/hc/en-us/articles/202521522-How-can-I-make-my-images-load-faster-
        $options['flags'] = 'lossy';

        // Always use secure https urls
        $options['secure'] = true;

        return Service::inst()->getCloudinaryUrl($this->PublicID, $options);
    }

    /**
     * @return String Link to the image in the Cloudinary media library.
     */
    public function getMediaLibraryLink() {
        return Controller::join_links(
            'https://cloudinary.com/console/media_library/asset/image',
            Service::config()->get('image_type'),
            $this->PublicID
        );
    }

    public function forTemplate() {
        return $this->getTag();
    }

    /**
     * Use custom gravity for image cropping if enabled.
     *
     * The coordinates may be set with the upload widget or through the management console.
     */
    private function addCustomGravityIfEnabled() {
        if (Service::config()->get('use_custom_gravity'))
            $this->transformOptions['gravity'] = 'custom';
    }

    /**
     * Scale the image to match exactly the given values.
     *
     * If both values are defined the image may be stretched or shrunk.
     * If only one value is defined the image is scaled with respect to the original aspect ratio.
     *
     * @param int $width
     * @param int $height
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#scale
     */
    public function Scale($width, $height) {
        if (!$width && !$height)
            return $this;

        $options = ['crop' => 'scale'];

        if ($width)
            $options['width'] = $width;

        if ($height)
            $options['height'] = $height;

        $this->transformOptions = $options;

        return $this;
    }

    /**
     * Scale the image to the given height with respect to the original aspect ratio.
     *
     * @param int $height
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#scale
     */
    public function ScaleHeight($height) {
        return $this->Scale(null, $height);
    }

    /**
     * Scale the image to the given width with respect to the original aspect ratio.
     *
     * @param int $width
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#scale
     */
    public function ScaleWidth($width) {
        return $this->Scale($width, null);
    }

    /**
     * Scale the image so it fits within the bounding box.
     *
     * Respects the original aspect ratio, scales down and up.
     *
     * @param int $width
     * @param int $height
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#fit
     */
    public function Fit($width, $height) {
        $this->transformOptions = [
            'crop'   => 'fit',
            'width'  => $width,
            'height' => $height
        ];

        return $this;
    }

    /**
     * Alias for Limit.
     *
     * @param int $width
     * @param int $height
     *
     * @return Image
     */
    public function FitMax($width, $height) {
        return $this->Limit($width, $height);
    }

    /**
     * Scale the image so it fits within the bounding box.
     *
     * Respects the original aspect ratio, no upscaling.
     *
     * @param int $width
     * @param int $height
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#limit
     */
    public function Limit($width, $height) {
        $this->transformOptions = [
            'crop'   => 'limit',
            'width'  => $width,
            'height' => $height
        ];

        return $this;
    }

    /**
     * Scale the image so it fits within the bounding box.
     *
     * Respects the original aspect ratio, only upscaling - larger images will stay larger.
     *
     * @param int $width
     * @param int $height
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#mfit_minimum_fit
     */
    public function FitMin($width, $height) {
        $this->transformOptions = [
            'crop'   => 'mfit',
            'width'  => $width,
            'height' => $height
        ];

        return $this;
    }

    /**
     * Crop the image to exact dimensions.
     *
     * Only parts of the image may be visible, scales down and up.
     *
     * @param int $width
     * @param int $height
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#fill
     */
    public function Fill($width, $height) {
        $this->transformOptions = [
            'crop'   => 'fill',
            'width'  => $width,
            'height' => $height
        ];

        $this->addCustomGravityIfEnabled();

        return $this;
    }

    /**
     * Alias for LimitFill.
     *
     * @param int $width
     * @param int $height
     *
     * @return Image
     */
    public function FillMax($width, $height) {
        return $this->LimitFill($width, $height);
    }

    /**
     * Crop the image to exact dimensions.
     *
     * Only parts of the image may be visible, no upscaling.
     *
     * @param int $width
     * @param int $height
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#lfill_limit_fill
     */
    public function LimitFill($width, $height) {
        $this->transformOptions = [
            'crop'   => 'lfill',
            'width'  => $width,
            'height' => $height
        ];

        $this->addCustomGravityIfEnabled();

        return $this;
    }

    /**
     * Scale to fit the bounding box, then pad whitespace.
     *
     * Retains the original aspect ratio and pads white space with a given color.
     *
     * @param int    $width
     * @param int    $height
     * @param string $background
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#pad
     */
    public function Pad($width, $height, $background = '#fff') {
        $this->transformOptions = [
            'crop'       => 'pad',
            'width'      => $width,
            'height'     => $height,
            'background' => $background
        ];

        return $this;
    }

    /**
     * Scale to fit the bounding box, then pad whitespace.
     *
     * Retains the original aspect ratio and pads white space with a given color - no upscaling.
     *
     * @param int    $width
     * @param int    $height
     * @param string $background
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#lpad_limit_pad
     */
    public function LimitPad($width, $height, $background = '#fff') {
        $this->transformOptions = [
            'crop'       => 'lpad',
            'width'      => $width,
            'height'     => $height,
            'background' => $background
        ];

        return $this;
    }

    /**
     * Scale to fit the bounding box, then pad whitespace.
     *
     * Retains the original aspect ratio and pads white space with a given color.
     * Only if the image is smaller than the given values, larger images will stay larger.
     *
     * @param int    $width
     * @param int    $height
     * @param string $background
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#mpad_minimum_pad
     */
    public function PadMin($width, $height, $background = '#fff') {
        $this->transformOptions = [
            'crop'       => 'mpad',
            'width'      => $width,
            'height'     => $height,
            'background' => $background
        ];

        return $this;
    }

    /**
     * Extract a region of the given width and height out of the original image.
     *
     * TODO this could be more powerful, Cloudinary supports adding the gravity (e.g. north_west)
     * or even define an exact starting point through x/y coordinates.
     *
     * @param int $width
     * @param int $height
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#crop
     */
    public function Crop($width, $height) {
        $this->transformOptions = [
            'crop'    => 'crop',
            'width'   => $width,
            'height'  => $height,
            'gravity' => 'center' // TODO make maintainable
        ];

        $this->addCustomGravityIfEnabled();

        return $this;
    }

    /**
     * Add a given effect.
     *
     * @param string $effect
     *
     * @return Image
     */
    private function addEffect($effect) {
        $this->effectOptions = ['effect' => $effect];

        return $this;
    }

    /**
     * Grayscale the image.
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#color_effects
     */
    public function Grayscale() {
        return $this->addEffect('grayscale');
    }

    /**
     * Add a sepia effect.
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#color_effects
     */
    public function Sepia() {
        return $this->addEffect('sepia');
    }

    /**
     * Append a blur filter.
     *
     * @param int $strength
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#blurring_pixelating_and_sharpening_effects
     */
    public function Blur($strength = null) {
        $effect = 'blur';
        if ($strength)
            $effect .= ':' . $strength;

        return $this->addEffect($effect);
    }

    /**
     * Pixelate the image with a given strength.
     *
     * @param int $strength
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#blurring_pixelating_and_sharpening_effects
     */
    public function Pixelate($strength = null) {
        $effect = 'pixelate';
        if ($strength)
            $effect .= ':' . $strength;

        return $this->addEffect($effect);
    }

    /**
     * Add one of the predefined artistic filter effects.
     *
     * @param string $filterName
     *
     * @return Image
     *
     * @see https://cloudinary.com/documentation/image_transformations#artistic_filter_effects
     */
    public function ArtisticFilter($filterName) {
        return (in_array($filterName, self::$artistic_filters)) ? $this->addEffect('art:' . $filterName) : $this;
    }

    public function getTag() {
        return '<img src="' . $this->Link() . '" alt="' . $this->Filename . '" />';
    }

    /**
     * Nice formatted file size of the original upload.
     *
     * @return string
     */
    public function getNiceSize() {
        return File::format_size($this->Size);
    }

    /**
     * @param int  $expires How long the URL should be valid as unix timestamp
     *
     * @param bool $asDownload
     *
     * @return string
     * @throws \Exception
     */
    public function getTemporaryDownloadLink($expires = null, $asDownload = true) {

        // Break if public id unknown
        if (!$this->PublicID)
            throw new Exception(_t('Level51\Cloudinary\Cloudinary.ERR_DOWNLOAD_NO_PUBLIC_ID', null, null, [
                'ImageID' => $this->ID
            ]));

        if (!$expires)
            $expires = Carbon::now()->addHour()->format('U');

        return Service::inst()->privateDownloadLink($this->PublicID, $this->Format, $expires, $asDownload);
    }
}
