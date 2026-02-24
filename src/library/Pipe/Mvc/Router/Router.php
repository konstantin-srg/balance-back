<?php
namespace Pipe\Mvc\Router;

use App\Public\Controller\IndexController;
use Pipe\Mvc\Router\Parser\IParser;
use Pipe\Mvc\Router\Parser\Literal;
use Pipe\Mvc\Router\Parser\Segment;

class Router
{
  protected Route $route;
  protected Request $request;
  protected Uri $uri;
  protected array $config;

  public function __construct(array $config)
  {
    $this->config = $config;
  }

  public function uri()
  {
    return $this->uri ?? $this->uri = new Uri();
  }

  public function route(): Route
  {
    if(isset($this->route)) {
      return $this->route;
    }

    $this->uri = new Uri();

    $default = [
      'module'      => 'App',
      'scenario'    => 'Public',
      'controller'  => 'Index',
      'action'      => 'index',
    ];

    $routeData = $this->go(
      'engine',
      [
        'type'     => Literal::class,
        'route'    => '',
        'weight'   => 1,
        'children' => $this->config,
      ],
      $this->uri()->path(),
    );

    return $this->route = $routeData ?
      new Route($routeData['vars']) :
      new Route([
          'action'     => 'error404',
          'controller' => 'Errors',
        ] + $default);
  }

  public function getRequest(): Request
  {
    return $this->request ?? $this->request = new Request($this->uri(), $this->route());
  }

  protected function go($name, $route, $url)
  {
    /** @var IParser $parser */
    $parser = new $route['type']();
    $result = $parser->match($route['route'], $url, empty($route['children']));

    if(!$result) {
      return false;
    }

    $vars = $route['defaults'] ?? [];
    $vars = $result['vars'] + $vars;

    if(!isset($route['children'])) {
      return [
        'weight'   => $route['weight'],
        'vars'     => $vars,
        'path'     => $name,
        'defaults'  => [
          'module'      => 'App',
          'controller'  => IndexController::class,
          'action'      => 'index',
        ],
      ];
    }

    $mRouter = null;
    $maxWeight = null;
    foreach ($route['children'] as $cName => $cData) {
      $cResult = $this->go($cName, $cData, $result['url']);
      if($cResult && ($maxWeight === null || $maxWeight < $cResult['weight'])) {
        $maxWeight = $cResult['weight'];
        $mRouter = $cResult;
      }
    }

    if($mRouter === null) {
      return false;
    }

    $vars  += $mRouter['vars'] ?? [];
    return [
      'weight'   => $route['weight'],
      'vars'     => ($mRouter['vars'] ?? []) + $vars,
      'path'     => $name . ' => ' . $mRouter['path'],
    ];
  }
}