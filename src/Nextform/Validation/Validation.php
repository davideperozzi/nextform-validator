<?php

namespace Nextform\Validation;

use Nextform\Config\AbstractConfig;
use Nextform\Fields\Validation\AbstractValidator;
use Nextform\Fields\Validation\ConnectionModel;
use Nextform\Helpers\ArrayHelper;
use Nextform\Helpers\FileHelper;
use Nextform\Validators\ConnectValidation;

class Validation
{
    /**
     * MD5 hash of "{{__nextform_value_undefined__}}"
     *
     * @var string
     */
    const VALUE_UNDEFINED = '129debbe81b69843f54ac33c1872b8af';

    /**
     * @var integer
     */
    const TYPE_DEFAULT = 0;

    /**
     * @var integer
     */
    const TYPE_EXCLUDE_FILE_VALIDATION = 1;

    /**
     * @var integer
     */
    const TYPE_ONLY_FILE_VALIDATION = 2;

    /**
     * @var ValidatorFactory
     */
    private $validatorFactory = null;

    /**
     * @var array
     */
    private $listeners = [];

    /**
     * @var array
     */
    private $models = [];

    /**
     * @var array
     */
    private $tmpData = [];

    /**
     * @var integer
     */
    private $type = self::TYPE_DEFAULT;

    /**
     * @var AbstractConfig
     */
    private $config = null;

    /**
     * @var array
     */
    private $ignore = [];

    /**
     * @param AbstractConfig $config
     * @param integer $type
     */
    public function __construct(AbstractConfig $config, $type = self::TYPE_DEFAULT)
    {
        $this->type = $type;
        $this->config = $config;
        $this->validatorFactory = new ValidatorFactory();

        $this->parseConfig($this->config);
        $this->setType($type);
    }

    /**
     * @return AbstractConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $name
     * @param callable $callback
     */
    public function addListener($name, $error, callable $callback)
    {
        if ( ! array_key_exists($name, $this->listeners)) {
            $this->listeners[$name] = [];
        }

        $this->listeners[$name][] = new Listeners\ValidationListener($name, $error, $callback);
    }

    /**
     * @param array $data
     * @param integer $type
     */
    public function addData($data)
    {
        $data = $this->parseInput($data);

        foreach ($data as $name => $value) {
            $this->tmpData[$name] = $value;
        }
    }

    /**
     * @param array $input
     * @return array
     */
    private function parseInput($input)
    {
        $parsedInput = [];

        foreach ($input as $name => $value) {
            if (FileHelper::isUploadedFile($value)) {
                $files = $this->createFileModels($value);
                if ( ! empty($files)) {
                    $parsedInput[$name] = $files;
                }
            } else {
                $parsedInput[$name] = $value;
            }
        }

        return $parsedInput;
    }

    /**
     * @param array $config
     * @return array
     */
    private function createFileModels($config)
    {
        $models = [];

        if (is_array($config['name'])) {
            $count = count($config['name']);

            for ($i = 0; $i < $count; $i++) {
                if (empty($config['name'][$i])) {
                    continue;
                }

                $models[] = new Models\FileModel(
                    $config['name'][$i],
                    $config['type'][$i],
                    $config['tmp_name'][$i],
                    $config['error'][$i],
                    $config['size'][$i]
                );
            }
        } else {
            if ( ! empty($config['name'])) {
                $models[] = new Models\FileModel(
                    $config['name'],
                    $config['type'],
                    $config['tmp_name'],
                    $config['error'],
                    $config['size']
                );
            }
        }

        return $models;
    }

    /**
     * @param AbstractConfig $config
     */
    private function parseConfig(AbstractConfig &$config)
    {
        $fields = $config->getFields();

        // Create all models
        foreach ($fields as $field) {
            $this->createModels($field);
        }
    }

    /**
     * @param integer $type
     */
    public function setType($type)
    {
        $this->type = $type;
        $this->ignore = [];
        $fields = $this->config->getFields();

        $this->filterModels($fields);
    }

    /**
     * @param array &$fields
     */
    private function filterModels(&$fields)
    {
        $ignore = [];

        foreach ($fields as $field) {
            if ($this->type == self::TYPE_EXCLUDE_FILE_VALIDATION) {
                if ($field->hasAttribute('type') &&
                    $field->getAttribute('type') == 'file') {
                    $ignore[] = $field->id;
                }
            } elseif ($this->type == self::TYPE_ONLY_FILE_VALIDATION) {
                if ($field->hasAttribute('type')) {
                    if ($field->getAttribute('type') != 'file') {
                        $ignore[] = $field->id;
                    }
                } else {
                    $ignore[] = $field->id;
                }
            }

            if ($field->hasChildren()) {
                $children = $field->getChildren();
                $this->filterModels($children);
            }
        }

        foreach ($ignore as $id) {
            if (array_key_exists($id, $this->models)) {
                $this->ignore[] = $id;
            }
        }
    }

    /**
     * @param AbstractField &$field
     */
    private function createModels(&$field)
    {
        if ( ! array_key_exists($field->id, $this->models)) {
            $this->models[$field->id] = [];
        }

        foreach ($field->getValidation() as $validation) {
            $this->models[$field->id][] = new Models\ValidationModel(
                $validation,
                $this->validatorFactory->create(
                    $validation->name,
                    $field,
                    $validation->value,
                    $validation->modifiers
                )
            );
        }

        if ($field->hasChildren()) {
            foreach ($field->getChildren() as $child) {
                $this->createModels($child);
            }
        }
    }

    /**
     * @param AbstractValidator &$validator
     * @param mixed $value
     * @return boolean
     */
    private function validateValue(&$validator, $value)
    {
        if ($value == self::VALUE_UNDEFINED) {
            if ($validator->validateUndefined()) {
                return $validator->validate(null);
            }

            return true;
        }

        if ( ! $validator->supports($value)) {
            throw new Exception\TypeNotSupportedException(
                sprintf(
                    'Value type "%s" not supported for validator "%s"',
                    gettype($value),
                    $this->validatorFactory->getName($validator)
                )
            );
        }

        return $validator->validate($value);
    }

    /**
     * @param array $input
     * @return Models\ResultModel
     */
    public function validate($input = [])
    {
        $result = new Models\ResultModel();
        $input = array_merge($this->tmpData, $this->parseInput($input));

        foreach ($this->models as $id => $validators) {
            if (in_array($id, $this->ignore)) {
                continue;
            }

            if (preg_match('/^(.*)\[.*\]$/', $id, $matches)) {
                $inputEntry = ArrayHelper::getSerializedArrayEntry($id);
                $value = array_key_exists($inputEntry, $input) ? $input[$inputEntry] : self::VALUE_UNDEFINED;
            } else {
                $value = array_key_exists($id, $input) ? $input[$id] : self::VALUE_UNDEFINED;
            }

            foreach ($this->models[$id] as $model) {
                if ($model->config->hasConnection()) {
                    if ( ! ($model->validator instanceof ConnectValidation)) {
                        throw new Exception\ConnectValidationNotSupportedException(
                            sprintf(
                                'Connect validation not supported for "%s" validator',
                                $model->config->name
                            )
                        );
                    }

                    $name = $model->config->value;

                    if ( ! array_key_exists($name, $this->models)) {
                        throw new Exception\ConnectedModelNotFound(
                            sprintf('Can\'t connect because model "%s" was not found', $name)
                        );
                    }

                    $connectInput = '';

                    if (array_key_exists($name, $input)) {
                        $connectInput = $input[$name];
                    }

                    if ($model->config->connection->action == ConnectionModel::ACTION_CONTENT) {
                        $model->validator->setOption($connectInput);

                        if ( ! $this->validateValue($model->validator, $value)) {
                            $result->addError($id, $model->config);
                        }
                    } elseif ($model->config->connection->action == ConnectionModel::ACTION_VALIDATE) {
                        $connectValidator = null;

                        foreach ($this->models[$name] as $connectModel) {
                            if ($connectModel->config->name == $model->config->name) {
                                $connectValidator = $connectModel->validator;
                            }
                        }

                        if (is_null($connectValidator)) {
                            throw new Exception\ValidatorNotFoundException(
                                sprintf('Validator for connected model "%s" not found', $model->config->name)
                            );
                        }

                        if ( ! $this->validateValue($connectValidator, $connectInput)) {
                            $result->addError($id, $model->config);
                        }
                    }
                } else {
                    if ( ! $this->validateValue($model->validator, $value)) {
                        $result->addError($id, $model->config);
                    }
                }
            }
        }

        foreach ($this->listeners as $id => $fieldListeners) {
            if (in_array($id, $this->ignore)) {
                continue;
            }

            foreach ($fieldListeners as $listener) {
                if (array_key_exists($id, $input)) {
                    if ( ! $listener->call($input[$id])) {
                        $result->addError($id, $listener->getError());
                    }
                }
            }
        }

        return $result;
    }
}
