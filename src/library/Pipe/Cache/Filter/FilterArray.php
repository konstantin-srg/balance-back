<?php
namespace Pipe\Cache\Filter;

use Laminas\Serializer\Adapter\PhpSerialize;
use Laravel\SerializableClosure\SerializableClosure;

class FilterArray implements IFilter
{
  protected $_closerMark = '__CLOSURE__';

  public function serialize($data): string
  {
    array_walk_recursive($data, function (&$item, $key) {
      if($item instanceof \Closure) {
        $item = $this->_closerMark . serialize(new SerializableClosure($item));
      }
    });

    return serialize($data);
  }

  public function unserialize(string $string): array
  {
    $data = unserialize($string);

    array_walk_recursive($data, function (&$item, $key) {
      if(is_string($item) && strpos($item, $this->_closerMark) === 0) {
        $item = unserialize(substr($item, strlen($this->_closerMark)))->getClosure();
      }
    });

    return $data;
  }
}