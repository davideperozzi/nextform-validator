<?php

namespace Nextform\Validation\Models;

use Nextform\Fields\Validation\ValidationModel as ConfigModel;
use Nextform\Validators\AbstractValidator;

class ValidationModel
{
    /**
     * @var ConfigModel
     */
    public $config = null;

    /**
     * @var Nextform\Validators\AbstractValidator
     */
    public $validator = null;

    /**
     * @param ConfigModel $model
     * @param AbstractValidator $Validators
     */
    public function __construct(ConfigModel $config, AbstractValidator $validator)
    {
        $this->config = $config;
        $this->validator = $validator;
    }
}
