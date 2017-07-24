<?php

namespace Nextform\Validators;

class EqualsValidator extends AbstractValidator implements ConnectValidation
{
    /**
     * @var string
     */
    public static $optionType = self::OPTION_TYPE_STRING;

    /**
     * @param string $value
     * @return boolean
     */
    public function validate($value)
    {
        return $this->option === $value;
    }
}
