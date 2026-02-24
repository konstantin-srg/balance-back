<?php
namespace Pipe\DModel\Containers;

class Boolean extends AbstractContainer
{
	const string SQL_TYPE = 'boolean';
	public function set($value)
	{
		return parent::set((int) $value);
	}
	public function get($options = null)
	{
		return (bool) $this->value;
	}

	public function unserialize($value)
	{
		$this->value = (int) $value;
		return $this;
	}

	public function serialize(): int
	{
		return $this->value;
	}
}