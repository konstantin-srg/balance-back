<?php

namespace Pipe\Cache;
use Laminas\Serializer\Adapter;

class Serializer
{
	protected Adapter\PhpSerialize $serializer;

	public function __construct()
	{
		$this->serializer = new Adapter\PhpSerialize();
	}

	public function serialize($data): string
	{
		return $this->serializer->serialize($data);
	}

	public function unserialize(string $data)
	{
		return $this->serializer->unserialize($data);
	}
}