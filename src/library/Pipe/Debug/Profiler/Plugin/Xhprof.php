<?php
namespace Pipe\Debug\Profiler\Plugin;

use Pipe\StdLib\TConfigurable;

class Xhprof implements IPlugin
{
  protected array $config = [
    'classes'   => true,
    'functions' => false,
    'ignore'    => [
      'Whoops\\',
      //'Pipe\\',
      'Laravel\\',
    ],
  ];

  public function start()
  {
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
  }

  public function timer($key) {}

  public function finish()
  {
    $prof = xhprof_disable();
    $result = [];

    $ignore = [
      'calls'  => 0,
      'cpu'    => 0,
      'memory' => 0,
    ];

    foreach ($prof as $key => $val) {
      $continue = false;
      $key = str_replace(['main()==>', 'load::', 'src/'], '', $key);

      foreach ($this->config['ignore'] as $exclude) {
        if(str_starts_with($key, $exclude)){
          $continue = true;

          $ignore['calls'] += $val['ct'];
          $ignore['cpu'] += $val['cpu'];
          $ignore['memory'] += $val['mu'];
          break;
        }
      }

      $continue =
        $continue &&
        ($this->config['classes'] || strpos($key, '::') !== false) &&
        ($this->config['functions'] || strpos($key, '::') === false) ;

      if($continue) continue;

      $result[$key] = [
        'calls'  => $val['ct'],
        'cpu'    => $val['cpu'],
        'memory' => $val['mu'],
      ];
    }

    $result['main()']['cpu'] -= $ignore['cpu'];
    //$result['main()']['memory'] -= $ignore['memory'];

    d($ignore);
    dd($result);
  }
}