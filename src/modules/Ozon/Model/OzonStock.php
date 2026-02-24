<?php
namespace Ozon\Model;

use Pipe\DModel\DModel;

/**
 * @property int $product_id
 * @property int $cluster_id
 * @property int $quantity
 * @property int $avg_sales
 */
class OzonStock extends DModel
{
	static public function setConfig()
	{
		return [
			'table' => 'ozon_stock',
			'properties' => [
				'oz_prod_id'    => [],
				'cluster_id'    => [],
				'quantity'      => [],
				'avg_sales'     => [],
			],
		];
	}
}