<?php
namespace Pipe\Mvc\Service;

use Pipe\Mvc\Engine\ISmAware;
use Pipe\Mvc\Engine\TSm;

class AService implements ISmAware
{
	use TSm;

	public function __construct($options = [])
	{
		$this->setSm($options['sm']);
	}
}