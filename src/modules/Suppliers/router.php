<?php
use Suppliers\Controller\SuppliersController;

return [
  '/suppliers/get' => [SuppliersController::class, 'getItem'],
  '/suppliers/list' => [SuppliersController::class, 'getItems'],
  '/suppliers/save' => [SuppliersController::class, 'saveItem'],
];