<?php
namespace Pipe\Debug\Profiler\Plugin;

use Pipe\Exception\Exception;

class Basic implements IPlugin
{
  protected array $steps  = [];
  protected array $start  = [];
  protected array $finish = [];

  public function start() {
    $this->timeCut($this->start);
  }

  public function timer($key) {
    $this->timeCut($this->steps[$key][(isset($this->steps[$key]['start']) ? 'finish' : 'start')]);
  }

  protected function timeCut(&$var)
  {
    $data = getrusage();
    $var = [
      'memory'  => $data['ru_maxrss'],
      'user'    => $data['ru_utime.tv_usec'],
      'system'  => $data['ru_stime.tv_usec'],
      'time'    => microtime(true),
    ];
  }

  public function finish() {
    $this->timeCut($this->finish);
    $steps = [];

    $this->steps['total'] = ['start' => &$this->start, 'finish' => &$this->finish];

    foreach ($this->steps as $key => $timer) {
      foreach ($timer['start'] as $var => $val) {
        if(!isset($timer['finish'])) {
          throw new Exception('Timer "' . $key . '" no ending ');
        }

        $steps[$key][$var] = $timer['finish'][$var] - $timer['start'][$var];
      }
    }

    dd($steps);
  }
}