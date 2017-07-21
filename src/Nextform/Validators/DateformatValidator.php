<?php

namespace Nextform\Validators;

class DateformatValidator extends AbstractValidator implements ConnectValidation
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
            $date = \DateTime::createFromFormat($this->option, $value);

            return $date && $date->format($this->option) == $value;
        }

        return false;
    }
}