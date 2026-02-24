<?php
namespace Pipe\File;

class File
{
  protected string $path;
  protected string | null $content = null;

  public function __construct(string $path = '')
  {
    $this->path = $path;
  }

  public function path(): string
  {
    return $this->path;
  }

  public function time(): int|false
  {
    return filemtime($this->path);
  }

	public function dirname(): string
	{
		return strstr($this->path, '\\');
	}

  public function filename(): string
  {
    return basename($this->path);
  }

  public function extension(): string | array
  {
    return pathinfo($this->path, PATHINFO_EXTENSION);
  }

  public function content(string $content = null): string | static
  {
    if($content !== null) {
      $this->content = $content;
      return $this;
    }

    if(!isset($this->content)) {
      $this->content = file_get_contents($this->path);
    }
    return $this->content;
  }
}