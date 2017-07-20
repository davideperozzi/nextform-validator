<?php

namespace Nextform\Validators;

class RegexValidator extends AbstractValidator implements ConnectValidation
{
	/**
	 * @var string
	 */
	public static $optionType = self::OPTION_TYPE_STRING;

	/**
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value) {
		if ( ! empty($this->option)) {
			return (bool)@preg_match($this->option, $value);
		}

		return false;
	}
}