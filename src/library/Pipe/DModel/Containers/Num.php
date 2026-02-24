<?php
namespace Pipe\DModel\Containers;

namespace Pipe\DModel\Containers;

class Num extends AbstractContainer
{
	public function unserialize($value)
	{
		$this->value = (double) $value;
		return $this;
	}

	public function get($options = null)
	{
		return (int) parent::get($this->value);
	}

  public function set($value)
  {
      return parent::set((int) $value);
  }

	public function serialize(): int
	{
		return (string) ($this->value ?? 0);
	}
}