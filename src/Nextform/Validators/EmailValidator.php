<?php

namespace Nextform\Validators;

class EmailValidator extends AbstractValidator implements ConnectValidation
{
    /**
     * @var string
     */
    public static $optionType = self::OPTION_TYPE_BOOLEAN;

    /**
     * @param string $value
     * @return boolean
     */
    public function validate($value)
    {
        if (true == $this->option) {
            return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
        }

        return true;
    }
}
