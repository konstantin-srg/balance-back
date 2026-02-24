<?php
namespace Pipe\StdLib;

trait TSingleton
{
  static protected $instance = null;

  static public function getInstance($config = []): static
  {
    return static::$instance ?? static::$instance = (new static($config));
  }


}