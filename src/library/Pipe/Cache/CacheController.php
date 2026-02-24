<?php

namespace Pipe\Cache;

use Pipe\Cache\Storage\IStorage;

class CacheController
{
  protected Serializer $serializer;
  protected IStorage $storage;

	public function __construct(IStorage $storage)
	{
		$this->storage = $storage;
		$this->serializer = new Serializer();
	}

  public function set(string $key, $val, int | bool $expire = false)
  {
		$this->storage->set($key, $this->serializer->serialize([
			'data'   => $val,
			'expire' => $expire ? time() + $expire : false,
		]));

    return $this;
  }

  public function get($key)
  {
		$result = $this->serializer->unserialize($this->storage->get($key));
		if($result == '') return null;


		if($result['expire'] !== false && $result['expire'] < time()) {
			return null;
		}

	  return $result['data'];
  }

  public function del($key)
  {
	  $this->storage->remove($key);
  }

  public function has($key)
  {
	  $this->storage->has($key);
  }
}