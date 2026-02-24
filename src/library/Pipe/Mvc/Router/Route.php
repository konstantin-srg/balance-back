<?php
namespace Pipe\Mvc\Router;

class Route
{
  protected string $scenario   = '';
  protected string $module     = '';
  protected string $controller = '';
  protected string $action     = '';
  public string $route      = '';

  protected array $vars        = [];

  public function __construct($vars)
  {
    foreach ($vars as $key => $val) {
      match ($key) {
        'scenario'   => $this->scenario = $val,
        'module'     => $this->module = $val,
        'controller' => $this->controller = $val,
        'action'     => $this->action = $val,
        'route'      => $this->route = $val,
        default      => $this->vars[$key] = $val,
      };
    }
  }

  public function scenario() { return $this->scenario; }
  public function module() { return $this->module; }
  public function controller() { return $this->controller; }
  public function action() { return $this->action; }
  public function route() { return $this->route; }
  public function vars() { return $this->vars; }
}