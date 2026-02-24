<?php

namespace Pipe\Debug;

class Timer
{
  protected int $start = 0;
  protected int $time = 0;

	public function __construct($autostart = true)
	{
		if($autostart) $this->start();
	}

	public function start()
	{
		$this->start = microtime(true);
	}

	public function stop()
	{
		$this->time += microtime(true) - $this->start;
		return $this;
	}

	public function get($precision = 4): float
	{
		return round($this->time > 0 ? $this->time : (microtime(true) - $this->start), $precision);
	}
}