<?php

namespace Nextform\Validation;

use Nextform\Config\AbstractConfig;
use Nextform\Validators\ConnectValidation;
use Nextform\Fields\Validation\AbstractValidator;
use Nextform\Fields\Validation\ConnectionModel;
use Nextform\Helpers\ArrayHelper;

class Validation
{
	/**
	 * @var ValidatorFactory
	 */
	private $validatorFactory = null;

	/**
	 * @var array
	 */
	private $models = [];

	/**
	 * @param AbstractConfig $config
	 */
	public function __construct(AbstractConfig $config) {
		$this->validatorFactory = new ValidatorFactory();
		$this->parseConfig($config);
	}

	/**
	 * @param AbstractConfig $config
	 */
	private function parseConfig(AbstractConfig &$config) {
		$fields = $config->getFields();

		foreach ($fields as $field) {
			$this->createModels($field);
		}
	}


	private function createModels(&$field) {
		if ( ! array_key_exists($field->id, $this->models)) {
			$this->models[$field->id] = [];
		}

		foreach ($field->getValidation() as $validation) {
			$this->models[$field->id][] = new Models\ValidationModel(
				$validation, $this->validatorFactory->create(
					$validation->name,
					$field,
					$validation->value,
					$validation->modifiers
				)
			);
		}

		if ($field->hasChildren()) {
			foreach ($field->getChildren() as $child) {
				$this->createModels($child);
			}
		}
	}

	/**
	 * @param AbstractValidator &$validator
	 * @param mixed $value
	 * @return boolean
	 */
	private function validateValue(&$validator, $value) {
		if ( ! $validator->supports($value)) {
			throw new Exception\TypeNotSupportedException(
				sprintf(
					'Value type "%s" not supported for validator "%s"',
					gettype($value),
					$this->validatorFactory->getName($validator)
				)
			);
		}

		return $validator->validate($value);
	}

	/**
	 * @param array $input
	 * @return Models\ResultModel
	 */
	public function validate($input = []) {
		$result = new Models\ResultModel();

		foreach ($this->models as $id => $validators) {
			if (preg_match('/^(.*)\[.*\]$/', $id, $matches)) {
				$inputEntry = ArrayHelper::getSerializedArrayEntry($id);
				$value = array_key_exists($inputEntry, $input) ? $input[$inputEntry] : '';
			}
			else {
				$value = array_key_exists($id, $input) ? $input[$id] : '';
			}

			foreach ($this->models[$id] as $model) {
				if ($model->config->hasConnection()) {
					if ( ! ($model->validator instanceof ConnectValidation)) {
						throw new Exception\ConnectValidationNotSupportedException(
							sprintf(
								'Connect validation not supported for "%s" validator',
								$model->config->name
							)
						);
					}

					$name = $model->config->value;

					if ( ! array_key_exists($name, $this->models)) {
						throw new Exception\ConnectedModelNotFound(
							sprintf('Can\'t connect because model "%s" was not found', $name)
						);
					}

					$connectInput = '';

					if (array_key_exists($name, $input)) {
						$connectInput = $input[$name];
					}

					if ($model->config->connection->action == ConnectionModel::ACTION_CONTENT) {
						$model->validator->setOption($connectInput);

						if ( ! $this->validateValue($model->validator, $value)) {
							$result->addError($id, $model->config);
						}
					}
					else if ($model->config->connection->action == ConnectionModel::ACTION_VALIDATE) {
						$connectValidator = null;

						foreach ($this->models[$name] as $connectModel) {
							if ($connectModel->config->name == $model->config->name) {
								$connectValidator = $connectModel->validator;
							}

						}

						if (is_null($connectValidator)) {
							throw new Exception\ValidatorNotFoundException(
								sprintf('Validator for connected model "%s" not found', $model->config->name)
							);
						}

						if ( ! $this->validateValue($connectValidator, $connectInput)) {
							$result->addError($id, $model->config);
						}
					}
				}
				else {
					if ( ! $this->validateValue($model->validator, $value)) {
						$result->addError($id, $model->config);
					}
				}
			}
		}

		return $result;
	}
}