<?php

namespace Nextform\Validators;

class ZipcodeValidator extends AbstractValidator implements ConnectValidation
{
	/**
	 * @var string
	 */
	public static $optionType = self::OPTION_TYPE_CSA;

	/**
     * @var array
     */
    private $patterns = array(
		'ar' => '/^[B-T]{1}\d{4}[A-Z]{3}$/i',
		'at' => '/^[0-9]{4}$/i',
		'au' => '/^[0-9]{4}$/i',
		'be' => '/^[1-9][0-9]{3}$/i',
		'ca' => '/^[a-z][0-9][a-z][ \t-]*[0-9][a-z][0-9]$/i',
		'ch' => '/^[0-9]{4}$/i',
		'cn' => '/^[0-9]{6}$/',
		'de' => '/^[0-9]{5}$/i',
		'dk' => '/^(DK-)?[0-9]{4}$/i',
		'ee' => '/^[0-9]{5}$/',
		'es' => '/^[0-4][0-9]{4}$/',
		'fi' => '/^(FI-)?[0-9]{5}$/i',
		'fr' => '/^(0[1-9]|[1-9][0-9])[0-9][0-9][0-9]$/i',
		'in' => '/^[1-9]{1}[0-9]{2}(\s|\-)?[0-9]{3}$/i',
		'it' => '/^[0-9]{5}$/',
		'is' => '/^[0-9]{3}$/',
		'lv' => '/^(LV-)?[1-9][0-9]{3}$/i',
		'mx' => '/^[0-9]{5}$/',
		'nl' => '/^[0-9]{4}.?([a-z]{2})?$/i',
		'no' => '/^[0-9]{4}$/',
		'nz' => '/^[0-9]{4}$/',
		'pl' => '/^[0-9]{2}-[0-9]{3}$/',
		'pt' => '/^[0-9]{4}-[0-9]{3}$/',
		'ru' => '/^[0-9]{6}$/',
		'se' => '/^[0-9]{3}\s?[0-9]{2}$/',
		'tr' => '/^[0-9]{5}$/',
		'uk' => '/^[a-z][a-z0-9]{1,3}\s?[0-9][a-z]{2}$/i',
		'us' => '/^[0-9]{5}((-| )[0-9]{4})?$/'
    );

	/**
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value) {
		foreach ($this->option as $locale) {
	        if (true == array_key_exists($locale, $this->patterns)) {
	            if (@preg_match($this->patterns[$locale], $value)) {
	            	return true;
	            }
	        }
		}

        return false;
	}
}