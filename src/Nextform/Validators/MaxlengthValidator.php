<?php

namespace Nextform\Validators;

class MaxlengthValidator extends AbstractValidator implements ConnectValidation
{
	/**
	 * @var string
	 */
	public static $optionType = self::OPTION_TYPE_INTEGER;

	/**
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value) {
		if (is_string($value) || is_numeric($value)) {
			return strlen((string) $value) <= $this->option;
		}

		return false;
	}
}
