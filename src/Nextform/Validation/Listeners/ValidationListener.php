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
    public function __construct($name, $error, callable $callback)
    {
        $this->name = $name;
        $this->error = $error;
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (is_callable([$this, $method])) {
            return call_user_func_array($this->$method, $args);
        }

        throw new \Exception(
                sprintf('Method %s not found in %s', $method, get_class($this))
            );
    }

    /**
     * @param mixed $value
     * @return boolean
     */
    public function call($value)
    {
        return (bool) $this->callback($value);
    }
}
