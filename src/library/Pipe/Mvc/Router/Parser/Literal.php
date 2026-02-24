<?php
namespace Pipe\Mvc\Router\Parser;

class Literal implements IParser
{
  public function match($route, string $url, $full = true): array | bool
  {
    $pathLen = strlen($url);
    $routeLen = strlen($route);

    if($pathLen < $routeLen || ($full && $pathLen > $routeLen) || !str_starts_with($url, $route)) return false;

    $result = [
      'vars'  => [],
    ];
    !$full ?: $result['url'] = substr($url, $routeLen);
    if(!$full) {
      $result['url'] = substr($url, $routeLen);
    }

    return $result;
  }
}