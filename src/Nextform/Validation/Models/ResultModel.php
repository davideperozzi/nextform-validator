<?php

namespace Nextform\Validation\Models;

use Nextform\Fields\Validation\ErrorModel;
use Nextform\Fields\Validation\ValidationModel as ConfigModel;

class ResultModel implements \JsonSerializable
{
    /**
     * @var string
     */
    const CUSTOM_PREFIX = 'custom_';

    /**
     * @var integer
     */
    private static $customCounter = 0;

    /**
     * @var boolean
     */
    private $valid = true;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param string $id
     * @param string|ConfigModel $model
     */
    public function addError($id, $model)
    {
        if (true == $this->valid) {
            $this->valid = false;
        }

        if (is_string($model)) {
            static::$customCounter++;

            $error = $model;
            $model = new ConfigModel(self::CUSTOM_PREFIX . static::$customCounter);
            $model->error = new ErrorModel($error);
        }

        if ( ! array_key_exists($id, $this->errors)) {
            $this->errors[$id] = [];
        }

        $this->errors[$id][] = $model;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'valid' => $this->valid,
            'errors' => $this->errors
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }
}
