<?php
namespace Pipe\Cache\Storage;

use Pipe\Exception\Exception;
use Pipe\StdLib\IConfigurable;

class APCu implements IStorage, IConfigurable
{
    protected array $config = [
        'ttl'   => 60*60
    ];
    public function setConfig($config)
    {
        if(!is_dir($config['folder'])) {
            throw new Exception('Folder "' . $config['filepath'] . '" not found');
        }
        $this->config = $config;
    }

  public function save($key, $string): bool
  {
    return apcu_store($key, $string, $this->config['ttl']);
  }

  public function load($key): string
  {
    return apcu_fetch($key);
  }

  public function remove($key): bool
  {
    return apcu_delete($key);
  }

  public function has($key): bool
  {
    return apcu_exists($key);
  }
}