<?php
namespace Pipe\DModeler\Old\DataAdapter;

trait TDa
{
  private AbstractAdapter $da;

	public function setDa(AbstractAdapter $da): void
	{
		/** @var DbAdapter da */
		$this->da = $da;
	}

  public function da(): AbstractAdapter
  {
    return $this->da;
  }
}