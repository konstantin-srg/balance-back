<?php
namespace Ozon\Model;

use Catalog\Model\Products;
use Pipe\DModel\Containers\Json;
use Pipe\DModel\DModel;
use Pipe\DModel\DModelList;


/**
 * @property string $name
 * @property integer $barcode
 * @property integer $ozon_id
 * @property integer $brand_id
 * @property integer $tag_id
 * @property Json $sku
 * @property string $image
 * @property integer $stock
 * @property integer $cost
 * @property bool $hidden
 *
 * @method DModelList<OzonStatistic> statistic($fields = [], $filters = [])
 */
class OzonProducts extends DModel
{
	static public function setConfig()
	{
		return [
			'table' => 'ozon_products',
			'properties' => [
				'ozon_id'    => 'int',
				'product_id' => 'int',
				'offer_id'   => 'int',
				'active'     => 'bool',
				'archived'   => 'bool',
			],
			'bonds' => [
				'product' => [
					'class'     => Products::class,
					'bond'      => ['product_id' => 'id'],
					'type'      => 'oto',
					'onDelete'  => null
				],
				'sku' => [
					'class'     => OzonProductsSku::class,
					'bond'      => ['id' => 'oz_prod_id'],
					'type'      => 'otm',
					'onDelete'  => 'remove'
				],
				'stock' => [
					'class'     => OzonStock::class,
					'bond'      => ['id' => 'oz_prod_id'],
					'type'      => 'otm',
					'onDelete'  => 'remove'
				],
				'statistic' => [
					'class'     => OzonStatistic::class,
					'bond'      => ['id' => 'oz_prod_id'],
					'type'      => 'oto',
					'onDelete'  => 'remove'
				],
				/*'statistic' => [
					'class'     => OzonStatistic::class,
					'bond'      => ['id' => 'ozon_product_id'],
					'type'      => 'oto',
					'onDelete'  => 'remove'
				],
				'stock' => [
					'class'     => OzonStock::class,
					'bond'      => ['id' => 'ozon_product_id'],
					'type'      => 'otm',
					'onDelete'  => 'remove'
				],*/
			]
		];
	}
}