<?php

namespace Nextform\Validation\Models;

use Nextform\Fields\Validation\ValidationModel as ConfigModel;

class ResultModel implements \JsonSerializable
{
	/**
	 * @var boolean
	 */
	private $valid = true;

	/**
	 * @var array
	 */
	private $errors = [];

	/**
	 * @param string $id
	 * @param ConfigModel $config
	 */
	public function addError($id, $config) {
		if (true == $this->valid) {
			$this->valid = false;
		}

		if ( ! array_key_exists($id, $this->errors)) {
			$this->errors[$id] = [];
		}

		$this->errors[$id][] = $config;
		$this->test = [];
	}

	/**
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * @return boolean
	 */
	public function isValid() {
		return $this->valid;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		return [
			'valid' => $this->valid,
			'errors' => $this->errors
		];
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return json_encode($this);
	}
}