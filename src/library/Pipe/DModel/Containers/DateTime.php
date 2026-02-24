<?php

namespace Pipe\DModel\Containers;

class DateTime extends AbstractContainer
{
	protected $value;

	public function set($dt)
	{
		$this->isChanged = true;

		if($dt instanceof \DateTime){
			$this->value = $dt;
		}

		if(strtolower($dt) === 'now'){
			if(!$this->value instanceof \DateTime) {
				$this->value = new \DateTime();
			}

			$this->value->setTimestamp(time());
		}

		try {
			$this->value = new \DateTime($dt);
			if(!$this->value) {throw new \Exception();}
		} catch (\Exception $e) {
			throw new \Exception('Can`t pars DateTime form "' . $dt . '"');
		}
	}

	public function get($options = null)
	{
		return $this;
	}

	public function format($format = 'Y-m-d H:i:s')
	{
		return $this->dt()->format($format);
	}

	public function isExpired($seconds)
	{
		$ts = $this->dt()->getTimestamp();
		return $ts <= 0 || ((time() - $this->dt()->getTimestamp()) > $seconds);
	}

	public function dt(): \DateTime
	{
		$this->isChanged(true);

		if(!$this->value instanceof \DateTime) {
			$this->value = new \DateTime('0000-00-00 00:00:00');
		}

		return $this->value;
	}

	public function unserialize($timestamp)
	{
		$this->value = new \DateTime($timestamp);
	}

	public function serialize():string
	{
		return $this->format('Y-m-d H:i:s');
	}

	public function __dump()
	{
		return $this->format() . ' - ' . ($this->isChanged ? 'changed' : 'not changed');
	}
}