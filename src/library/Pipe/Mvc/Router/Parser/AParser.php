<?php
namespace Pipe\Mvc\Engine\Parser;

use Pipe\StdLib\TConfigurable;
use Pipe\StdLib\TSingleton;

abstract class AParser implements IParser
{
  protected string $route;
  protected array $defaults;

  public function __construct(string $route, array $defaults = [])
  {
    $this->route = $route;
    $this->defaults = $defaults;
  }

  public function match(string $path): self
  {
  }

  public function getVars(): array
  {

  }
}