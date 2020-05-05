<?php

namespace Level51\Cloudinary;

use ReflectionClass;

/**
 * Multi file upload field for cloudinary using their javascript widget.
 *
 * @package Level51\Cloudinary
 */
class MultiUploadField extends UploadField {

    /**
     * @var array
     *        - field: string, e.g. MyDataObjectID
     *        - id: number, ID of the DataObject to which the images should be linked
     */
    private $relation;

    /**
     * @var array|null Holder for already stored images
     */
    private $files;

    /**
     * @var int The number of files allowed for this field
     */
    private $allowedMaxFileNumber = 10;

    public function getPayload($asJSON = true) {
        $options = parent::getPayload(false);

        $options = array_merge($options, [
            'type'     => 'multi',
            'relation' => $this->relation,
            'files'    => $this->getFilesPayload()
        ]);

        unset($options['file']);

        $options['cloudinaryOptions']['cropping'] = false;
        $options['cloudinaryOptions']['maxFiles'] = $this->getAllowedMaxFileNumber();

        return $asJSON ? json_encode($options) : $options;
    }

    public function setValue($value, $record = null) {
        $relName = $this->getName();

        if ($record && isset($record->ID)) {

            // Remember the relation field name and id - passed in the options array for usage after the upload is done
            $this->setRelation((new ReflectionClass($record))->getShortName() . 'ID', $record->ID);

            // Get already stored files
            if ($record->hasMethod($relName))
                $this->files = $record->$relName();
        }
    }

    /**
     * Get already stored files if there are any
     * @return mixed
     */
    public function getFiles() {
        return $this->files;
    }

    /**
     * Get the flat image info of each stored file.
     *
     * @return array
     */
    public function getFilesPayload() {
        $payload = [];

        if ($this->getFiles()) {
            foreach ($this->getFiles() as $file) {
                $payload[] = $file->flatten();
            }
        }

        return $payload;
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

    /**
     * Limit the amount of files that can be uploaded.
     *
     * @param int $count
     *
     * @return $this
     */
    public function setAllowedMaxFileNumber($count) {
        $this->allowedMaxFileNumber = $count;

        return $this;
    }

    /**
     * Get the max file limit.
     *
     * @return int
     */
    public function getAllowedMaxFileNumber() {
        return $this->allowedMaxFileNumber;
    }
}
