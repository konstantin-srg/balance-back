<?php

namespace Pipe\DModel\Containers;

use Laminas\Json\Json as ZJson;
use Laminas\Stdlib\ArrayObject as ArrayObject;

class Json extends AbstractContainer
{
	//protected bool $touched = false;

	public function set($value)
	{
		$this->isChanged = true;
		//$this->touched = true;
		$this->value = new ArrayObject($value, 2);;
		$this->value = $this->ArrToObj($value);

		return $this;
	}

	public function __get($name)
	{
		return $this->value->get($name);
	}

	public function get($trace = null)
	{
		$this->isChanged = true;
		//$this->touched = true;
		if ($this->value === null) {
			$this->unserialize('');
		}

		/*if ($trace === null) {
			$trace = ltrim($trace, '[');
			$trace = str_replace(']', '', $trace);
			$trace = explode('[', $trace);

			$value = $this->value;
			for ($i = 0; $i < count($trace); $i++) {
				$value = $value->{$trace[$i]};
			}
		}*/

		return $this->value;
	}

	public function unserialize($value)
	{
		try {
			$this->value = $this->ArrToObj(ZJson::decode($value, ZJson::TYPE_ARRAY));
			//$this->value = new ArrayObject(ZJson::decode($this->source, ZJson::TYPE_ARRAY),2);
		} catch (\Exception $e) {
			$this->value = new ArrayObject([], 2);
		}

		return $this->value;
	}

	public function serialize(): string
	{
		return ZJson::encode($this->value ? $this->ObjToArr($this->value) : new \StdClass());
	}

	/*public function isChanged($changed = null)
	{
		if ($changed === null && $this->touched) {
			$jsonStr = ZJson::encode($this->value ? $this->ObjToArr($this->value) : new \StdClass());
			if ($this->value !== $jsonStr) {
				return true;
			}
			return false;
		}

		return parent::isChanged($changed);
	}*/

	/*public function setSource($value)
	{
			parent::setSource($value);
			$this->unserialize();

			return $this;
	}

	public function getSource()
	{
			return $this->source;
	}*/

	public function toArray(): array
	{
		return $this->value ? $this->value->getArrayCopy() :[];
	}

	protected function ArrToObj($array)
	{
		$obj = new ArrayObject([], 2);
		foreach ($array as $key => $val) {
			if (strlen($key)) {
				if (is_array($val)) {
					$obj->{$key} = $this->ArrToObj($val);
				} else {
					$obj->{$key} = $val;
				}
			}
		}
		return $obj;
	}

	protected function ObjToArr($obj)
	{
		if ($obj instanceof ArrayObject) {
			$obj = $obj->getArrayCopy();
		}

		foreach ($obj as $key => $val) {
			$obj[$key] = $this->ObjToArr($val);
		}

		return $obj;
	}

	public function __dump()
	{
		return [
			//'val'     => $this->value,
			'value'     => $this->ObjToArr($this->value),
			'changed' => ($this->isChanged ? 'changed' : 'not changed')
		];
	}
}