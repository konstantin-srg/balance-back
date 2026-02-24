<?php
namespace Pipe\Cache\Storage;

use Pipe\Exception\Exception;
use Pipe\StdLib\IConfigurable;

class Filesystem implements IStorage
{
  protected string $dir;

	public function __construct(string $cacheDir)
	{
		if(!is_dir($cacheDir)) {
			throw new Exception("Folder '{$cacheDir}'' not found");
		}
		$this->dir = $cacheDir;
	}

  protected function getFilename($key): string
  {
    return $this->dir . '/' . $key . '.txt';
  }

  public function set($key, $val): bool
  {
    return (bool) file_put_contents($this->getFilename($key), $val);
  }

  public function get($key): string
  {
    return (string) file_get_contents($this->getFilename($key));
  }

  public function remove($key): bool
  {
    return unlink($this->getFilename($key));
  }

  public function has($key): bool
  {
    return file_exists($this->getFilename($key));
  }
}