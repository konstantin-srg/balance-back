<?php
namespace Pipe\DModel\Containers;

use Pipe\DateTime\Date as ADate;

class Date extends AbstractContainer
{
    /**
     * @var \DateTime null
     */
    protected $value = null;

    public function set($value)
    {
        $this->value = ADate::getDT($value);

        $this->isChanged(true);

        return $this;
    }

    public function get($options = null)
    {
        $this->isChanged(true);

        if(isset($options['dt'])) {
            return $this->value;
        }

        if($this->value) return $this->value;

        return $this->unserialize();
    }

    public function unserialize()
    {
        $this->value = ADate::getDT($this->source);

        return $this->value;
    }

    public function serialize()
    {
        $this->source = $this->value ? $this->value->format('Y-m-d') : '0000-00-00';

        return $this->source;
    }

    public function serializeArray($result = [], $prefix = '') {
        return $this->get()->format('d.m.Y');
    }
}