<?php
namespace Pipe\Debug\Console;

use Pipe\StdLib\TSingleton;

class Console
{
  use TSingleton;

  protected $blocks = [];

  public function __construct()
  {

  }
}