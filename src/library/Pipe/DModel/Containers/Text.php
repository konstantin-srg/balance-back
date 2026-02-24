<?php
namespace Pipe\DModel\Containers;

class Text extends AbstractContainer
{
	public function setSource($value)
	{
		$this->source = (string) $value;
		return $this;
	}
}