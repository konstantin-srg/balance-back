<?php
namespace Balance\Model;

use Pipe\DModel\Containers\DateTime;
use Pipe\DModel\DModel;

/**
 * @property string $name
 * @property string $key
 * @property int $sum
 * @property DateTime $date
 */
class Flow extends DModel
{
	static public function setConfig()
	{
		return [
			'table' => 'ozon_finances',
			'properties' => [
				'name'    => [],
				'sum'     => 'int',
				'date'    => ['type' => 'datetime'],
				'key'     => [],
			],
		];
	}
}