<?php

/**
 * Field for multiple Cloudinary file uploads.
 */
class CloudinaryMultiUploadField extends CloudinaryUploadFormField {

    /**
     * @var array
     *        - field: string, e.g. MyDataObjectID
     *        - id: number, ID of the DataObject to which the images should be linked
     */
    private $relation;

    /**
     * @var array|null Holder for already stored images
     */
    private $items;

    public function Field($properties = array()) {
        Requirements::javascript(SILVERSTRIPE_CLOUDINARY_DIR . '/dist/cloudinary-multi-upload-field.js');
        Requirements::css(SILVERSTRIPE_CLOUDINARY_DIR . '/dist/cloudinary-multi-upload-field.css');

        return parent::Field($properties);
    }

    public function getOptions() {
        $options = parent::getOptions();

        $options = array_merge($options, [
            'relation' => $this->relation
        ]);

        return Convert::array2json($options);
    }

    public function setValue($value, $record = null) {
        $relName = $this->getName();

        if ($record && isset($record->ID)) {

            // Remember the relation field name and id - passed in the options array for usage after the upload is done
            $this->setRelation($record->class . 'ID', $record->ID);

            // Get already stored items
            if ($record->hasMethod($relName))
                $this->items = $record->$relName();
        }
    }

    /**
     * Get already stored items if there are any
     * @return mixed
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * Set the relation from the CloudinaryImage to the rel. object.
     *
     * @param string $fieldName e.g. ImageOwnerID
     * @param int    $ID        ID of the owner object
     *
     * @return $this
     */
    public function setRelation($fieldName, $ID) {
        $this->relation = [
            'field' => $fieldName,
            'id'    => $ID
        ];

        return $this;
    }
}
