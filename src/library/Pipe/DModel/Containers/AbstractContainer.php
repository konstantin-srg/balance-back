<?php

namespace Pipe\DModel\Containers;

class AbstractContainer
{
	const string SQL_TYPE = 'varchar';

	protected array $options;

	protected bool $isChanged = false;
	protected $value = null;

	public function set($value)
	{
		$this->value = $value;
		$this->isChanged = true;

		return $this;
	}

	public function get($options = null)
	{
		return $this->value;
	}
	public function clear()
	{
		$this->value = null;
		return $this;
		//return $this->unserialize('');
	}

	public function unserialize($value)
	{
		$this->value = (string) $value;
		return $this;
	}

	public function serialize(): string | int | null
	{
		return (string) $this->value;
	}

	public function isChanged($changed = null)
	{
		if ($changed === null) return $this->isChanged;
		$this->isChanged = $changed;
		return $this;
	}

	public function __toString()
	{
		return $this->serialize();
	}

	public function setOptions($options)
	{
		$this->options = $options;
	}

	public function save() {}
	public function remove() {}

	public function __dump()
	{
		return $this->get() . ' - ' . ($this->isChanged ? 'changed' : 'not changed');
	}
}