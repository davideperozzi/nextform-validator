<?php

namespace Nextform\Validators;

use Nextform\Fields\AbstractField;

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
	public function __construct(AbstractField $field, $option = '') {
		$this->field = $field;
		$this->setOption($option);
	}

	/**
	 * @param string $option
	 */
	public function setOption($option) {
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
		}

		$this->option = $option;
	}

	/**
	 * @param mixed $value
	 * @return boolean
	 */
	public function supports($value) {
		return in_array(gettype($value), static::$supportedTypes);
	}

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function addModifier($name, $value) {
		if ( ! array_key_exists($name, static::$supportedModifiers)) {
			if (in_array($name, static::$supportedModifiers)) {
				throw new Exception\ModifierNotSupportedException(
					sprintf('Invalid type for modifier "%s" in "%s".
						Type definition reuqired', $name, get_class($this))
				);
			}
			else {
				throw new Exception\ModifierNotSupportedException(
					sprintf('Modifier "%s" not supported for validator "%s"', $name, get_class($this))
				);
			}
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
		}
		else {
			throw new Exception\ModifierNotSupportedException(
				sprintf('Invalid modifier types for "%s" modifier found', $name)
			);
		}

		$this->modifiers[$name] = $value;
	}

	/**
	 * @param mixed $value
	 * @return boolean
	 */
	abstract public function validate($value);
}