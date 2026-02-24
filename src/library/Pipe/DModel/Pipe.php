<?php

namespace Pipe\DModel;

class Pipe
{
	protected $source = [];
	protected $indexes = [];

	public function setSource(&$source)
	{
		$this->source  = &$source;
		$this->indexes = array_keys($source);
		return $this;
	}

	public function find($filter)
	{
		if(is_array($filter)) {
			$filter = function($dmodel) use ($filter) {
				foreach ($filter as $field => $value) {
					if(is_array($value) && !in_array($dmodel->$field, $value)) {
						return false;
					} elseif($dmodel->$field != $value) {
						return false;
					}
				}
				return true;
			};
		}

		foreach ($this->indexes as $ii => $i) {
			if(!$filter($this->source[$i])) {
				unset($this->indexes[$ii]);
			}
		}

		return $this;
	}

	public function other()
	{
		$this->indexes = array_diff_key(array_keys($this->source), $this->indexes);
		return $this;
	}

	/**
	 * @param callable(DModel):static $func
	 * @return $this
	 */
	public function map(callable $func)
	{
		foreach ($this->indexes as $i) {
			$func($this->source[$i]);
		}
		return $this;
	}
	public function remove()
	{
		foreach ($this->indexes as $ii => $i) {
			$this->source[$i]->remove();
			unset($this->indexes[$ii]);
			unset($this->source[$i]);
		}
		return $this;
	}
	public function nothing($func)
	{
		$func();
		return $this;
	}
	public function arr(string | callable $func = null)
	{
		if(is_string($func)) {
			$func = function($dmodel, &$arr) use ($func) { $arr[] = $dmodel->$func; };
		}

		$arr = [];
		foreach ($this->indexes as $i) {
			$func($this->source[$i], $arr);
		}

		return $arr;
	}

	/*public function one($func)
	{
		if($this->indexes) {
			$func($this->source[current($this->indexes)]);
		}
	}*/

	public function current()
	{
		return $this->source[current($this->indexes)];
	}

	public function count()
	{
		return count($this->indexes);
	}

	public function one(): DModel | false
	{
		if(!$this->indexes) return false;

		return $this->source[current($this->indexes)];
	}
}