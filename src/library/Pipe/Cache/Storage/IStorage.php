<?php
namespace Pipe\Cache\Storage;

interface IStorage
{
  public function set(string $key, string $val): bool;
  public function get(string $key): string;
  public function has(string $key): bool;
  public function remove(string $key): bool;
}