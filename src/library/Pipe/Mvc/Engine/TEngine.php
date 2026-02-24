<?php
namespace Pipe\Mvc\Engine;

trait TEngine
{
  private Engine $engine;

  public function setEngine(Engine $engine)
  {
    $this->engine = $engine;
  }

  public function engine(): Engine
  {
    return $this->engine;
  }
}