<?php
namespace Pipe\Mvc\Engine;

trait TSm
{
	protected ServiceManager $sm;

	public function setSm(ServiceManager $sm)
	{
		$this->sm = $sm;
	}
  public function sm($service = null)
  {
    return $service ? $this->sm->get($service) : $this->sm;
  }
}