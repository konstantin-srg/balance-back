<?php
namespace Pipe\Observer;

use ArrayAccess;

class Event
{
    protected $caller;
    protected $params = [];

    public function __construct($caller = null, $params = null)
    {
        $this->setCaller($caller);
        $this->setParams($params);
    }

    /**
     * @return string|object
     */
    public function caller()
    {
        return $this->caller;
    }

    /**
     * Set parameters
     *
     * Overwrites parameters
     *
     * @param  array|ArrayAccess|object $params
     * @throws \Exception
     */
    public function setParams($params)
    {
        if (!is_array($params) && ! is_object($params)) {
            throw new \Exception(
                sprintf('Event parameters must be an array or object; received "%s"', gettype($params))
            );
        }

        $this->params = $params;
    }

    /**
     * @return array|object|ArrayAccess
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param  string|int $name
     * @param  mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (is_array($this->params) || $this->params instanceof ArrayAccess) {
            if (! isset($this->params[$name])) {
                return $default;
            }

            return $this->params[$name];
        }

        if (! isset($this->params->{$name})) {
            return $default;
        }
        return $this->params->{$name};
    }

    /**
     * @param  null|string|object $caller
     */
    public function setCaller($caller)
    {
        $this->caller = $caller;
    }

    /**
     * @param  string|int $name
     * @param  mixed $value
     */
    public function setParam($name, $value)
    {
        if (is_array($this->params) || $this->params instanceof ArrayAccess) {
            $this->params[$name] = $value;
            return;
        }

        $this->params->{$name} = $value;
    }
}