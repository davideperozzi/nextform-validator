<?php

namespace Nextform\Validation\Models;

class FileModel
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $type = '';

    /**
     * @var string
     */
    public $tmpName = '';

    /**
     * @var integer
     */
    public $error = 0;

    /**
     * @var integer
     */
    public $size = 0;

    /**
     * @param string $name
     * @param string $type
     * @param string $tmpName
     * @param integer $error
     * @param integer $size
     */
    public function __construct($name, $type, $tmpName, $error, $size) {
        $this->name = $name;
        $this->type = $type;
        $this->tmpName = $tmpName;
        $this->error = $error;
        $this->size = $size;
    }

    /**
     * @return boolean
     */
    public function isValid() {
        return ! empty($this->name) && $this->error == 0;
    }
}