<?php
namespace Pipe\Debug\Profiler\Plugin;

interface IPlugin
{
  public function start();
  public function timer($key);
  public function finish();
}