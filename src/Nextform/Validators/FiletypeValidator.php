<?php

namespace Nextform\Validators;

class FiletypeValidator extends AbstractValidator implements ConnectValidation
{
	/**
	 * @var string
	 */
	public static $optionType = self::OPTION_TYPE_CSA;

    /**
     * @var array
     */
    public static $supportedTypes = [
        'array<Nextform\Validation\Models\FileModel>'
    ];

	/**
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value) {
		if (is_array($value) &&  ! empty($value)) {
            $validExtensions = 0;

            foreach ($value as $file) {
                $foundExtensions = 0;

                for ($i = 0, $len = count($this->option); $i < $len; $i++) {
                    $possibleExtension = substr($file->name, -strlen($this->option[$i]));

                    if (strtolower($possibleExtension) == strtolower($this->option[$i])) {
                        $foundExtensions++;
                    }
                }

                if ($foundExtensions > 0) {
                    $validExtensions++;
                }
            }

            return $validExtensions == count($value);
        }

        return false;
	}
}