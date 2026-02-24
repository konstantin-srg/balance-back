<?php
namespace Pipe\Debug\Profiler;

use Pipe\Debug\Profiler\Plugin\Basic;
use Pipe\Debug\Profiler\Plugin\IPlugin;
use Pipe\Debug\Profiler\Plugin\Xhprof;
use Pipe\StdLib\TSingleton;

class Profiler
{
  use TSingleton;

  /** @var IPlugin[] */
  protected array $plugins = [];

  protected function __construct()
  {
    $this->plugins = [
      //new Xhprof(),
      new Basic(),
    ];
  }

  public function start()
  {
    foreach ($this->plugins as $plugin) {
      $plugin->start();
    }
    return $this;
  }

  public function finish()
  {
    foreach ($this->plugins as $plugin) {
      $plugin->finish();
    }
  }

  public function addStep($key)
  {
    foreach ($this->plugins as $plugin) {
      $plugin->timer($key);
    }
    return $this;
  }
}