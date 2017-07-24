<?php

namespace Nextform\Validators;

class MaxsizeValidator extends AbstractValidator implements ConnectValidation
{
    /**
     * @var string
     */
    public static $optionType = self::OPTION_TYPE_INTEGER;

    /**
     * @var array
     */
    public static $supportedTypes = [
        'array<Nextform\Validation\Models\FileModel>'
    ];

    /**
     * @param array $value
     * @return boolean
     */
    public function validate($value)
    {
        if (is_array($value)) {
            foreach ($value as $file) {
                if (intval($file->size / 1000) > $this->option) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
