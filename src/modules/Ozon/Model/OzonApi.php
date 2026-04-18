<?php
namespace Ozon\Model;

use Pipe\DModel\DModel;

/**
 * @property string $key
 * @property string $name
 */
class OzonApi extends DModel
{
	static public function setConfig()
	{
		return [
			'table' => 'ozon_api',
			'properties' => [
				'name'   => [],
				'key'    => [],
				'pass'    => [],
			],
		];
	}
}