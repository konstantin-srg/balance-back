<?php
namespace Balance\Model;

use Pipe\DModel\Containers\DateTime;
use Pipe\DModel\DModel;

/**
 * @property string $name
 * @property int $sum
 * @property DateTime $date
 */
class Balance extends DModel
{
	static public function setConfig()
	{
		return [
			'table' => 'balance',
			'properties' => [
				'name'    => [],
				'sum'     => 'int',
				'date'    => ['type' => 'datetime'],
			],
		];
	}
}