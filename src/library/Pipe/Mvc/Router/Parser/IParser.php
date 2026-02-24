<?php
namespace Pipe\Mvc\Router\Parser;

interface IParser
{
  public function match($route, string $url, $full = true): array | bool;
}