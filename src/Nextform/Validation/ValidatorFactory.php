<?php

namespace Nextform\Validation;

use Nextform\Validators\AbstractValidator;
use Nextform\Validators\RequiredValidator;
use Nextform\Validators\MaxlengthValidator;
use Nextform\Validators\MinlengthValidator;
use Nextform\Validators\EqualsValidator;

class ValidatorFactory
{
	/**
	 * @var array
	 */
	private $validators = [
		'required' => RequiredValidator::class,
		'maxlength' => MaxlengthValidator::class,
		'minlength' => MinlengthValidator::class,
		'equals' => EqualsValidator::class
	];

	/**
	 * @param AbstractValidator $validator
	 * @return string
	 */
	public function getName(AbstractValidator $validator) {
		foreach ($this->validators as $name => $ctor) {
			if ($validator instanceof $ctor) {
				return $name;
			}
		}

		return '';
	}

	/**
	 * @param string $name
	 * @return boolean
	 */
	public function has($name) {
		return array_key_exists($name, $this->validators);
	}

	/**
	 * @param string $name
	 * @param AbstractField $field
	 * @param string $option
	 * @param array $modifiers
	 * @return AbstractValidator
	 */
	public function create($name, $field, $option = '', $modifiers = []) {
		if ( ! array_key_exists($name, $this->validators)) {
			throw new Exception\ValidatorNotFoundException(
				sprintf('Validator "%s" not found', $name)
			);
		}

		$ctor = $this->validators[$name];
		$validator = new $ctor($field, $option);

		foreach ($modifiers as $name => $value) {
			$validator->addModifier($name, $value);
		}

		return $validator;
	}
}