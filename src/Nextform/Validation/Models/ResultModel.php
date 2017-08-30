<?php

namespace Nextform\Validation\Models;

use Nextform\Fields\Validation\ErrorModel;
use Nextform\Fields\Validation\ValidationModel as ValidationConfigModel;
use Nextform\Validation\Exception\InvalidErrorModelException;

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
     * @param string|ValidationConfigModel $modelOrMessage
     */
    public function addError(string $id, $modelOrMessage)
    {
        if (true == $this->valid) {
            $this->valid = false;
        }

        // Create a model if message is given
        if (is_string($modelOrMessage)) {
            static::$customCounter++;

            $model = new ValidationConfigModel(self::CUSTOM_PREFIX . static::$customCounter);
            $model->error = new ErrorModel($modelOrMessage);
        } elseif ($modelOrMessage instanceof ValidationConfigModel) {
            $model = $modelOrMessage;
        } else {
            throw new InvalidErrorModelException('Invalid validation model given');
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
