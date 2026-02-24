<?php
namespace Pipe\Cache\Filter;

class FilterObject extends FilterString
{
  protected $string = '';

  public function set($data)
  {
    $this->string = serialize($data);
  }

  public function get()
  {
    return unserialize($this->string);
  }
}