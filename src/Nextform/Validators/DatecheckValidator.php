<?php

namespace Nextform\Validators;

class DatecheckValidator extends AbstractValidator implements ConnectValidation
{
	/**
	 * @var string
	 */
	public static $optionType = self::OPTION_TYPE_BOOLEAN;

	/**
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value) {
        if (true == $this->option) {
            $time = strtotime($value);

            if ($time) {
                $date = explode("-", date("Y-m-d", $time));

                list($year, $month, $day) = $date;

                return checkdate($month, $day, $year);
            }
            else {
                return false;
            }
        }

        return true;
	}
}