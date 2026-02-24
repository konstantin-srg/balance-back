<?php
namespace Ozon\Model;

use Pipe\DModel\DModel;


/**
 * @property int $ozon_id
 * @property string $name
 * @property bool $russia
 * @property int $sort
 */
class OzonClusters extends DModel
{
	static public function setConfig()
	{
		return [
			'table' => 'ozon_clusters',
			'properties' => [
				'ozon_id'   => [],
				'name'      => [],
				'russia'    => ['type' => 'boolean'],
				'sort'      => ['type' => 'integer'],
			],
		];
	}

}