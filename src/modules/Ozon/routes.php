<?php
use Ozon\Controller\OzonController;
use Ozon\Controller\OzonSyncController;

return [
	'/ozon/sync/test'   => [OzonSyncController::class, 'test'],
	'/ozon/sync'        => [OzonSyncController::class, 'sync'],
	'/ozon/sync/map'    => [OzonSyncController::class, 'map'],
	'/ozon/stock/table' => [OzonController::class, 'getStockTable'],
	'/ozon/accounts/list' => [OzonController::class, 'getAccountsList'],
];