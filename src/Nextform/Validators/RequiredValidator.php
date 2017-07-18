<?php

namespace Nextform\Validators;

use Nextform\Fields\CollectionField;
use Nextform\Helpers\ArrayHelper;

class RequiredValidator extends AbstractValidator implements ConnectValidation, CollectionValidation
{
	/**
	 * @var string
	 */
	public static $optionType = self::OPTION_TYPE_BOOLEAN;

	/**
	 * @var array
	 */
	public static $supportedModifiers = [
		'min' => 'integer',
		'max' => 'integer'
	];

	/**
	 * @var array
	 */
	public static $supportedTypes = [
		'string',
		'integer',
		'array'
	];

	/**
	 *
	 */
	public function validate($value) {
		if (true == $this->option) {
			if ($this->field instanceof CollectionField) {
				if (is_array($value) && ! empty($value)) {
					$reducedValues = ArrayHelper::serializeArrayKeys($value);
					$pathPrefix = $this->field->getAttribute('name');
					$validateValues = [];

					foreach ($reducedValues as $path => $value) {
						if (array_key_exists($path, $validateValues)) {
							continue;
						}

						foreach ($this->field->getChildren() as $child) {
							$childName = $child->getAttribute('name');
							$collectionPath = $pathPrefix . $path;

							if ($childName == $collectionPath) {
								$validateValues[$path] = $value;
							}
						}
					}

					if (empty($validateValues)) {
						return false;
					}
					else {
						$validValues = [];

						foreach ($validateValues as $path => $values) {
							if (is_array($values)) {
								foreach ($values as $value) {
									$validValues[] = $this->isEmpty($value) ? 0 : 1;
								}
							}
							else {
								$validValues[] = $this->isEmpty($values) ? 0 : 1;
							}
						}

						$valueResultsCount = array_count_values($validValues);

						// Validate min modifier
						if (array_key_exists('min', $this->modifiers)) {
							if ($valueResultsCount[1] < $this->modifiers['min']) {
								return false;
							}
						}

						// Validate max modifier
						if (array_key_exists('max', $this->modifiers)) {
							if ($valueResultsCount[1] > $this->modifiers['max']) {
								return false;
							}
						}
					}

					return true;
				}
			}
			else {
				if (is_array($value) && ! empty($value)) {
					if ($this->isArrayField()) {
						$reducedValues = ArrayHelper::serializeArrayKeys($value);
						$fieldPath = $this->field->getAttribute('name');
						$pathPrefix = ArrayHelper::getSerializedArrayEntry($fieldPath);

						foreach ($reducedValues as $path => $values) {
							if ($pathPrefix . $path == $fieldPath) {
								if (is_array($values)) {
									$validValues = [];

									foreach ($values as $value) {
										$validValues[] =  $this->isEmpty($value) ? 0 : 1;
									}

									$valueResultsCount = array_count_values($validValues);

									return array_key_exists(1, $valueResultsCount);
								}
								else {
									return ! $this->isEmpty($values);
								}
							}
						}
					}
					else {
						// @todo: Validate other types (e.g. files)
					}

					return false;
				}
				else if (is_string($value) || is_numeric($value)) {
					return !$this->isEmpty($value);
				}
			}

			return false;
		}

		return true;
	}



	/**
	 * @return boolean
	 */
	private function isArrayField() {
		if ($this->field->hasAttribute('name')) {
			return ArrayHelper::isSerializedArray(
				$this->field->getAttribute('name')
			);
		}

		return false;
	}

	/**
	 * @param string $str
	 * @return boolean
	 */
	private function isEmpty($str) {
		return trim(preg_replace('/ |\t|\r|\r\n/', '', $str)) == '';
	}
}
