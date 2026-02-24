<?php
namespace Ozon\Model;

use Pipe\DModel\Containers\DateTime;
use Pipe\DModel\DModel;


/**
 * @property string $key
 * @property DateTime $sync
 */
class OzonSync extends DModel
{
	static public function setConfig()
	{
		return [
			'table' => 'ozon_sync',
			'properties' => [
				'key'   => [],
				'sync'  => ['type' => 'datetime'],
			],
		];
	}

	public function isExpired($seconds)
	{
		return $this->sync->isExpired($seconds);
	}
}