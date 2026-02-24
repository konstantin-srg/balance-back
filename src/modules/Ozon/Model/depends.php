<?php

use Ozon\Model;

return [
	Model\Test::class => [
		'parent'      => Model\Test::class,
		'field'       => 'parent',
		'type'        => 'mto',
		'independent' => true,
	],
];