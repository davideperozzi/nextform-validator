<?php

namespace Nextform\Validators;

use Nextform\Fields\CollectionField;
use Nextform\Helpers\ArrayHelper;
use Nextform\Validation\Models\FileModel;

class RequiredValidator extends AbstractValidator implements ConnectValidation, CollectionValidation
{
    /**
     * @var string
     */
    public static $optionType = self::OPTION_TYPE_BOOLEAN;

    /**
     * @var boolean
     */
    public static $validateUndefinedValues = true;

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
        'array<Nextform\Validation\Models\FileModel>',
        'array',
        'string',
        'integer'
    ];

    /**
     * @param string|array $value
     * @return boolean
     */
    public function validate($value)
    {
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

                    $validValues = [];

                    foreach ($validateValues as $path => $values) {
                        if (is_array($values)) {
                            foreach ($values as $value) {
                                $validValues[] = $this->isEmpty($value) ? 0 : 1;
                            }
                        } else {
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


                    return true;
                } elseif (is_string($value)) {
                    return ! $this->isEmpty($value);
                }
            } else {
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
                                        if ($value instanceof FileModel) {
                                            $validValues[] = $value->isValid() ? 1 : 0;
                                        } else {
                                            $validValues[] =  $this->isEmpty($value) ? 0 : 1;
                                        }
                                    }

                                    $valueResultsCount = array_count_values($validValues);

                                    return array_key_exists(1, $valueResultsCount);
                                }

                                return ! $this->isEmpty($values);
                            }
                        }
                    } else {
                        if ( ! empty($value)) {
                            $valueCount = count($value);
                            $validCount = 0;

                            foreach ($value as $val) {
                                if ($val instanceof FileModel) {
                                    if ($val->isValid()) {
                                        $validCount++;
                                    }
                                } elseif (is_string($val) && ! $this->isEmpty($val)) {
                                    $validCount++;
                                } elseif ( ! empty($val)) {
                                    $validCount++;
                                }
                            }

                            return $validCount > 0;
                        }

                        return ! empty($value);
                    }
                } elseif (is_string($value) || is_numeric($value)) {
                    return ! $this->isEmpty($value);
                }
            }

            return false;
        }

        return true;
    }

    /**
     * @param string $str
     * @return boolean
     */
    private function isEmpty($str)
    {
        return trim(preg_replace('/ |\t|\r|\r\n/', '', $str)) == '';
    }
}
