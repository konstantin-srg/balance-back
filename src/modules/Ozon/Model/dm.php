<?php

use Ozon\Model\Test;
use Ozon\Model\OzonProducts;

return [
	\Ozon\Model\OzonStatistic::class => [
		'product' => [
			'parent' => OzonProducts::class,
			'type'   => 'mto',
		],
	],
	Test::class => [
		'parent_id' => [
			'parent' => Test::class,
			'type'   => 'otm',
			'independent' => true,
			'actions' => [
				//DModel::ACTION_ON_DELETE => 'delete',
			],
		],
	],
];