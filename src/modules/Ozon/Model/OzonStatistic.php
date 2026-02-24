<?php
namespace Ozon\Model;

use Pipe\DModel\Containers\DateTime;
use Pipe\DModel\DModel;

/**
 * @property int $oz_prod_id
 * @property int $sales
 * @property int $profit
 * @property int $commission
 * @property int $stock
 */
class OzonStatistic extends DModel
{
	static public function setConfig()
	{
		return [
			'table' => 'ozon_products_statistic',
			'properties' => [
				'oz_prod_id' => 'int',
				'sales'      => 'int',
				'profit'     => 'int',
				'commission' => 'int',
				'stock'      => 'int',
			],
		];
	}
}