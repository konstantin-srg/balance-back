<?php
namespace Pipe\Mvc\Controller;

use Pipe\DModeler\Old\TDm;
use Pipe\Mvc\Engine\TEngine;
use Pipe\Mvc\Engine\TSm;

abstract class AController
{
  use TEngine, TSm;

  protected array $params;

  public function __construct(array $params)
  {
    $this->params = $params;
  }

  public function params(string $key = null, $escape = true, $default = null)
  {
		if($key === null) {
			return $this->params;
		}
		if(isset($this->params[$key])) {
			return $escape ? htmlspecialchars($this->params[$key]) : $this->params[$key];
		} else {
			return $default;
		}
  }

	public function service()
	{
		$serviceClass = str_replace('Controller', 'Service', get_called_class());
		return $this->sm()->get($serviceClass);
	}
}