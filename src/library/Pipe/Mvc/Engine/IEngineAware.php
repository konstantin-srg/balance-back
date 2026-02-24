<?php
namespace Pipe\Mvc\Engine;

interface IEngineAware
{
  public function setEngine(ServiceManager $sm);
}