<?php
use Supplies\Controller\SuppliersController;

return [
  '/supplies/get' => [SuppliersController::class, 'getItem'],
  '/supplies/list' => [SuppliersController::class, 'getItems'],
  '/supplies/add' => [SuppliersController::class, 'saveItem'],
];