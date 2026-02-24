<?php
namespace Pipe\File;

use Pipe\Debug\Profiler\Plugin\Basic;
use Pipe\Debug\Profiler\Plugin\IPlugin;
use Pipe\Debug\Profiler\Plugin\Xhprof;
use Pipe\Exception\Exception;
use Pipe\StdLib\TSingleton;

class Director
{
  use TSingleton;

  protected FilesList $files;
  protected array $dirs;

  public function __construct($folder = ''){
    $this->files = new FilesList();
    if($folder) $this->scan($folder);
  }

  public function younger($time): static
  {
    $this->files->del(4);
    foreach ($this->files as $key => $file) {
      if($file->time() > $time) {
        $this->files->del($key);
      }
    }

    return $this;
  }

  public function ifChanged($timeOrFile, $callbackTrue = null, $callbackFalse = null): static
  {
    if($timeOrFile) {
      if(is_string($timeOrFile)) {
        $time = file_exists($timeOrFile) ? filemtime($timeOrFile) : 0;
      }

      $changed = false;
      $this->files->del(4);
      foreach ($this->files as $key => $file) {
        if($file->time() > $time) {
          $changed = true;
          break;
        }
      }
    } else {
      $changed = true;
    }

    if(!$changed) {
      $this->files->flush();
      if($callbackFalse) {
        call_user_func_array($callbackFalse, []);
      }
    } elseif($callbackTrue) {
      call_user_func_array($callbackTrue, []);
    }

    return $this;
  }

  public function getFiles(): FilesList
  {
    return $this->files;
  }

  public function extension($extensions): static
  {
    $extensions = (array) $extensions;
    foreach ($this->files as $key => $file) {
      foreach ((array) $file->extension() as $extension) {
        if(!in_array($extension, $extensions)) {
          $this->files->del($key);
        }
      }
    }

    return $this;
  }

  public function each($callback): static
  {
    foreach ($this->files as $file) {
      $file->content(call_user_func_array($callback, [$file]));
    }
    return $this;
  }

  public function exclude($pathList): static
  {
    $pathList = (array) $pathList;
    foreach ($this->files as $key => $file) {
      if(in_array($file->path(), $pathList)) {
        $this->files->del($key);
      }
    }

    return $this;
  }

  public function all($callback): static
  {
    call_user_func_array($callback, [$this->files]);
    return $this;
  }

  public function one($callback): static
  {
    if(count($this->files)) {
      $file = $this->files->rewind()->current();
      $file->content(call_user_func_array($callback, [$file]));
    }
    return $this;
  }

  public function concat(): static
  {
    if(count($this->files) < 2) {
      return $this;
    }
    $result = '';
    foreach($this->files as $file) {
      $result .= $file->content();
    }
    $this->files = (new FilesList())->addFile((new File())->content($result));

    return $this;
  }

  public function save($path): static
  {
    if(count($this->files) > 1) {
      $this->concat();
    } elseif(count($this->files) === 0) {
      return $this;
    }

    file_put_contents($path, $this->files->rewind()->current()->content());

    return $this;
  }

  public function remove(): static
  {
    foreach ($this->files as $file) {
      if(file_exists($file->path())) {
        unlink($file->path());
      }
    }

    return $this;
  }

  public function dump()
  {
    $this->each(function ($file) {
      d($file);
    });
		return $this;
  }

  public function scan($pattern, $depth = 50)
  {
    if(strpos($pattern, '*') !== false) {
      $baseDir = strstr($pattern, '*', true);

      $dirSep = DIRECTORY_SEPARATOR == '\\' ? '\\\\' : '/';
      $pattern = str_replace(['\\', '/'], $dirSep, $pattern);

      if(str_contains($pattern, '**') === false) {
        $depth = max(substr_count ($pattern, '\\*\\'), 1);
      }

      $pattern = str_replace(['**', $dirSep . '*'], ['.*', $dirSep . '[\w\\.]+'], $pattern);
      $pattern = "|^{$pattern}$|";
    } else {
      $baseDir = $pattern;
      $pattern = false;
    }

    if(!is_dir(realpath($baseDir))) {
      throw new Exception('Not a dir: "' . $baseDir . '"');
    }

    $this->scanDir($baseDir, $pattern, $depth);

    return $this;
  }

  protected function scanDir($dir, $pattern, $depth = 50)
  {
    foreach (scandir($dir) as $key => $value) {
      $path = realpath($dir . DIRECTORY_SEPARATOR . $value);

			if($value == '..') {
				continue;
			}

      if (!is_dir($path)) {
        if(!$pattern || preg_match($pattern, $path)) {
          $this->files->add($path);
        }
      } else {
				if($depth && $value != ".") {
					$this->scanDir($path, $pattern, $depth - 1);
				}
      }
    }

    return $this;
  }
}