<?php
namespace Pipe\Debug\Dumper;

class Renderer
{
  protected string $skin  = __DIR__ . '/skins/main.css';
  protected string $theme = __DIR__ . '/skins/php-storm.css';

  public function layout($class, $html)
  {
    return
      ($class == 'window' ? '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Noto+Sans+Mono">' : '').
      '<div class="dumper main ' . $class . '">'.
        '<style>'.
          (file_get_contents($this->skin) . file_get_contents($this->theme)).
        '</style>'.
        '<div class="wrap">' . $html . '</div>'.
      '</div>';
  }

  public function row($type, $key, $val)
  {
    return
      '<div class="row ' . strtolower($type) . '">'.
        ($key !== '' ? '<div class="key">' . $this->type('var', $key) . $this->type('sign', ':', 'colon') . '</div>' : '') .
        '<div class="val">' . $this->type($type, $val) . '</div>'.
      '</div>';
  }

  public function caller($data, $time)
  {
    return
      '<div class="caller">' . $data['file'] . ' ' . $data['line'] . ' <span class="time">' . $time . ' sec</span></div>';
  }

  public function folder($type, $key, $val, $brackets = ['[', ']'], $open = false)
  {
    $html =
      '<div class="row ' . strtolower($type) . ' ' . ($open ? 'open' : '') . '">';

    if(!$val) {
      $html .=
        ($key ? '<div class="key">' .
          $this->type('var', $key, 'class')  .
          $this->type('sign', $brackets[0] . $brackets[1], 'bracket') .
          '</div>' : ''
        ).
        '</div>';

      return $html;
    }

    $html .=
        ($key ? '<div class="key">' .
          $this->type('var', $key, 'class') .
          $this->type('sign', $brackets[0], 'bracket') .
          $this->type('sign', '>', 'arrow') .
          '</div>' : ''
        ).
        '<div class="val">' . $val . '</div>'.
        '<span class="sign">' . $brackets[1] . '</span>'.
      '</div>';

    return $html;
  }

  public function type($type, $val, $class = '')
  {
    $class = match (strtolower($type)) {
      'string'  => 'str',
      'integer' => 'int',
      'double'  => 'dbl',
      'floar'   => 'flt',
      'array'   => 'arr',
      default   => $type,
    } . ' ' . $class;

    return '<span class="' . $class . '">' . $val . '</span>';
  }
}