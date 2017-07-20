<?php

namespace Nextform\Validation\Listeners;

class ValidationListener
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $error = '';

    /**
     * @var callable
     */
    private $callback = null;

    /**
     * @param string $name
     * @param callable $callback
     */
    public function __construct($name, $error, callable $callback) {
        $this->name = $name;
        $this->error = $error;
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getError() {
        return $this->error;
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    public function call($value) {
        return (bool) ($this->callback)($value);
    }
}