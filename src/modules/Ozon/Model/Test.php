<?php
namespace Ozon\Model;

use Pipe\DModel\Containers\Json;
use Pipe\DModel\DModel;


/**
 * @property string $name
 * @property string $test
 * @property Json $asd
 */
class Test extends DModel
{
	static public function setConfig()
	{
		return [
			'table' => 'test',
			'properties' => [
				'name'  => [],
				'size'  => ['default' => 'big'],
				'test'  => ['type' => 'bool', 'default' => false],
				'asd'   => ['type' => 'json'],
			],
		];
	}

}