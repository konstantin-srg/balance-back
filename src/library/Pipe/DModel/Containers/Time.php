<?php
namespace Pipe\DModel\Containers;

use Pipe\DateTime\Time as ATime;

class Time extends AbstractContainer
{
    /**
     * @var \DateTime null
     */
    protected $value = null;

    public function set($value)
    {
        $this->value = ATime::getDT($value);

        $this->isChanged(true);

        return $this;
    }

    public function get($options = null)
    {
        $this->isChanged(true);

        if($this->value) return $this->value;

        return $this->unserialize();
    }

    public function unserialize()
    {
        if(!$this->source) {
            return $this->value = ATime::getDT();
        }

        $this->value = ATime::getDT($this->source);

        return $this->value;
    }

    public function serialize()
    {
        if(!$this->value) {
            $this->value = ATime::getDT($this->source);
        }

        $this->source = $this->value->format('H:i:s');

        return $this->source;
    }

    public function serializeArray() {
        return $this->get()->format('H:i:s');
    }
}