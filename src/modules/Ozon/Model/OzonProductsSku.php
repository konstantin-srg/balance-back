<?php
namespace Ozon\Model;

use Catalog\Model\Products;
use Pipe\DModel\DModel;

/**
 * @property int $product_id
 * @property string $sku
 */
class OzonProductsSku extends DModel
{
	static public function setConfig()
	{
		return [
			'table' => 'ozon_products_sku',
			'properties' => [
				'oz_prod_id' => 'int',
				'sku'        => 'string',
				'active'     => 'bool',
			],
			'bonds' => [
				'ozon' => [
					'class'     => OzonProducts::class,
					'bond'      => ['oz_prod_id' => 'id'],
					'type'      => 'oto',
					'onDelete'  => null
				],
			],
		];
	}
}