<?php

use Balance\Controller\BalanceController;
use Balance\Controller\FlowController;

return [
  '/flow/products'     => [FlowController::class, 'flowProducts'],
	'/flow/flow-add'     => [FlowController::class, 'flowAdd'],
	'/flow/flow-del'     => [FlowController::class, 'flowDel'],
	'/flow/flow-list'    => [FlowController::class, 'flowList'],

  '/balance/products'  => [BalanceController::class, 'balance'],
  '/balance/flow-add'  => [BalanceController::class, 'flowAdd'],
  '/balance/flow-del'  => [BalanceController::class, 'flowDel'],
  '/balance/flow-list' => [BalanceController::class, 'flowList'],
];