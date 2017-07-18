<?php

namespace Nextform\Validators;

class MinlengthValidator extends AbstractValidator implements ConnectValidation
{
	/**
	 * @var string
	 */
	public static $optionType = self::OPTION_TYPE_INTEGER;

	/**
	 *
	 */
	public function validate($value) {
		if (is_string($value) || is_numeric($value)) {
			return strlen((string) $value) >= $this->option;
		}

		return false;
	}
}