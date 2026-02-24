<?php
namespace Pipe\File;

use JetBrains\PhpStorm\Pure;

class FilesList implements \Countable, \Iterator {
  protected int $position = 0;

  /** @var File[]  */
  protected array $files = [];

  public function add($path): static {
    $this->files[] = new File($path);
    return $this;
  }

  public function addFile(File $file): static {
    $this->files[] = $file;
    return $this;
  }

  public function del($key): static {
    unset($this->files[$key]);
    return $this;
  }

  public function flush()
  {
    $this->files = [];
  }

  #[\ReturnTypeWillChange] public function rewind(): static {
    $this->files = array_values($this->files);
    $this->position = 0;
    return $this;
  }

  public function current() :File
  {
    return $this->files[$this->position];
  }

  public function key() :int
  {
    return $this->position;
  }

  public function next(): void {
    ++$this->position;
  }

  public function valid(): bool {
    return isset($this->files[$this->position]);
  }

  public function count(): int {
    return count($this->files);
  }
}