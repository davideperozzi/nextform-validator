<?php

namespace Nextform\Validation;

use Nextform\Validators\AbstractValidator;
use Nextform\Validators\DatecheckValidator;
use Nextform\Validators\DateformatValidator;
use Nextform\Validators\EmailValidator;
use Nextform\Validators\EqualsValidator;
use Nextform\Validators\FiletypeValidator;
use Nextform\Validators\MaxlengthValidator;
use Nextform\Validators\MaxsizeValidator;
use Nextform\Validators\MinlengthValidator;
use Nextform\Validators\MinsizeValidator;
use Nextform\Validators\RegexValidator;
use Nextform\Validators\RequiredValidator;
use Nextform\Validators\ZipcodeValidator;
use Nextform\Validators\MaxcountValidator;
use Nextform\Validators\MincountValidator;

class ValidatorFactory
{
    /**
     * @var array
     */
    private $validators = [
        'dateformat' => DateformatValidator::class,
        'datecheck' => DatecheckValidator::class,
        'maxlength' => MaxlengthValidator::class,
        'minlength' => MinlengthValidator::class,
        'maxcount' => MaxcountValidator::class,
        'mincount' => MincountValidator::class,
        'filetype' => FiletypeValidator::class,
        'required' => RequiredValidator::class,
        'maxsize' => MaxsizeValidator::class,
        'minsize' => MinsizeValidator::class,
        'zipcode' => ZipcodeValidator::class,
        'equals' => EqualsValidator::class,
        'regex' => RegexValidator::class,
        'email' => EmailValidator::class
    ];

    /**
     * @param AbstractValidator $validator
     * @return string
     */
    public function getName(AbstractValidator $validator)
    {
        foreach ($this->validators as $name => $ctor) {
            if ($validator instanceof $ctor) {
                return $name;
            }
        }

        return '';
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function has($name)
    {
        return array_key_exists($name, $this->validators);
    }

    /**
     * @param string $name
     * @param AbstractField $field
     * @param string $option
     * @param array $modifiers
     * @return AbstractValidator
     */
    public function create($name, $field, $option = '', $modifiers = [])
    {
        if ( ! array_key_exists($name, $this->validators)) {
            throw new Exception\ValidatorNotFoundException(
                sprintf('Validator "%s" not found', $name)
            );
        }

        $ctor = $this->validators[$name];
        $validator = new $ctor($field, $option);

        foreach ($modifiers as $name => $value) {
            $validator->addModifier($name, $value);
        }

        return $validator;
    }
}
