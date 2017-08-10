<?php

namespace Nextform\Validators;

use Nextform\Fields\AbstractField;
use Nextform\Helpers\ArrayHelper;

abstract class AbstractValidator
{
    /**
     * @var string
     */
    const OPTION_TYPE_STRING = 1;

    /**
     * @var string
     */
    const OPTION_TYPE_INTEGER = 2;

    /**
     * @var string
     */
    const OPTION_TYPE_FLOAT = 3;

    /**
     * @var string
     */
    const OPTION_TYPE_BOOLEAN = 4;

    /**
     * Comma seperated array
     *
     * @var string
     */
    const OPTION_TYPE_CSA = 5;

    /**
     * @var string
     */
    public static $optionType = self::OPTION_TYPE_STRING;

    /**
     * @var array
     */
    public static $supportedModifiers = [];

    /**
     * @var array
     */
    public static $supportedTypes = [
        'string', 'integer'
    ];

    /**
     * @var boolean
     */
    public static $validateUndefinedValues = false;

    /**
     * @var mixed
     */
    protected $option = '';

    /**
     * @var AbstractField
     */
    protected $field = null;

    /**
     * @var array
     */
    protected $modifiers = [];

    /**
     * @param string $option
     */
    public function __construct(AbstractField $field, $option = '')
    {
        $this->field = $field;
        $this->setOption($option);
    }

    /**
     * @return AbstractField
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $option
     */
    public function setOption($option)
    {
        switch (static::$optionType) {
            case self::OPTION_TYPE_STRING:
                $option = (string) $option;
                break;

            case self::OPTION_TYPE_BOOLEAN:
                $option = filter_var($option, FILTER_VALIDATE_BOOLEAN);
                break;

            case self::OPTION_TYPE_INTEGER:
                $option = intval($option);
                break;

            case self::OPTION_TYPE_FLOAT:
                $option = floatval($option);
                break;

            case self::OPTION_TYPE_CSA:
                $option = explode(',', $option);

                for ($i = 0, $len = count($option); $i < $len; $i++) {
                    $option[$i] = trim($option[$i]);
                }
                break;
        }

        $this->option = $option;
    }

    /**
     * @return boolean
     */
    public function validateUndefined()
    {
        return static::$validateUndefinedValues;
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    public function supports($value)
    {
        return is_null($value) || in_array($this->getExtendedType($value), static::$supportedTypes);
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function getExtendedType($value)
    {
        if (is_array($value)) {
            $typeCount = [];

            foreach ($value as $val) {
                if ( ! is_object($val)) {
                    continue;
                }

                $className = get_class($val);

                if (array_key_exists($className, $typeCount)) {
                    $typeCount[$className]++;
                } else {
                    $typeCount[$className] = 1;
                }
            }

            if (count($typeCount) == 1) {
                reset($typeCount);

                return gettype($value) . '<' . key($typeCount) . '>';
            }
        }

        return gettype($value);
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addModifier($name, $value)
    {
        if ( ! array_key_exists($name, static::$supportedModifiers)) {
            if (in_array($name, static::$supportedModifiers)) {
                throw new Exception\ModifierNotSupportedException(
                    sprintf('Invalid type for modifier "%s" in "%s".
						Type definition reuqired', $name, get_class($this))
                );
            }

            throw new Exception\ModifierNotSupportedException(
                    sprintf('Modifier "%s" not supported for validator "%s"', $name, get_class($this))
                );
        }

        $supportedModifierType = static::$supportedModifiers[$name];

        if (is_string($supportedModifierType)) {
            if ($supportedModifierType != gettype($value)) {
                if ( ! @settype($value, $supportedModifierType)) {
                    throw new Exception\ModifierNotSupportedException(
                        sprintf(
                            'Invalid modifier type given for "%s" modifier.
							Casting from "%s" to "%s" failed in "%s"',
                            $name,
                            gettype($value),
                            $supportedModifierType,
                            get_class($this)
                        )
                    );
                }
            }
        } else {
            throw new Exception\ModifierNotSupportedException(
                sprintf('Invalid modifier types for "%s" modifier found', $name)
            );
        }

        $this->modifiers[$name] = $value;
    }

    /**
     * @return boolean
     */
    protected function isArrayField()
    {
        if ($this->field->hasAttribute('name')) {
            return ArrayHelper::isSerializedArray(
                $this->field->getAttribute('name')
            );
        }

        return false;
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    abstract public function validate($value);
}
