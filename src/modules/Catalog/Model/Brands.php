<?php
namespace Catalog\Model;

use Pipe\DModel\DModel;

/**
 * @property string $name
 * @property string $short
 */
class Brands extends DModel
{
	static public function setConfig()
	{
		return [
			'table' => 'wh_brands',
			'properties' => [
				'name'     => [],
				'short'    => [],
			],
		];
	}
}