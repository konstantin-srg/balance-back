<?php
namespace Pipe\Mvc\Engine;

trait TServiceManager
{
  private ServiceManager $sm;

  public function setSm(ServiceManager $sm)
  {
    $this->sm = $sm;
  }

  public function sm(): ServiceManager
  {
    return $this->sm;
  }
}