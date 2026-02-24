<?php
namespace Pipe\Cache\Filter;

class FilterString implements IFilter
{
  public function serialize($data): string
  {
    return (string) $data;
  }

  public function unserialize(string $string): string
  {
    return $string;
  }
}