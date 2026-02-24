<?php
namespace Catalog\Model;

use Ozon\Model\OzonProducts;
use Pipe\DModel\DModel;

/**
 * @property string  $name
 * @property integer $barcode
 * @property integer $brand_id
 * @property integer $tag_id
 * @property string  $image
 * @property integer $cost
 * @property bool $hidden
 */
class Products extends DModel
{
	static public function setConfig()
	{
		return [
			'table' => 'wh_products',
			'properties' => [
				'brand_id'=> [],
				'tag_id'  => 'int',
				'barcode' => [],
				'name'    => [],
				'image'   => [],
				'cost'    => 'int',
				'sort'    => 'int',
				'active'  => ['type' => 'bool', 'default' => 1],
			],
			'bonds' => [
				'brand' => [
					'class'     => Brands::class,
					'bond'      => ['brand_id' => 'id'],
					'type'      => 'oto',
					'onDelete'  => null
				],
				'ozon' => [
					'class'     => OzonProducts::class,
					'bond'      => ['id' => 'product_id'],
					'type'      => 'oto',
				],
			]
		];
	}
}