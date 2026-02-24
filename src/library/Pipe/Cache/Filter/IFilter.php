<?php
namespace Pipe\Cache\Filter;

interface IFilter
{
  public function serialize($data): string;

  public function unserialize(string $string);
}