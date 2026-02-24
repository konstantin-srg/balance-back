<?php

use Catalog\Controller\BalanceController;
use Catalog\Controller\ProductController;

return [
  '/catalog/products/get' => [ProductController::class, 'getItem'],
  '/catalog/products/list' => [ProductController::class, 'getItems'],
  '/catalog/products/add' => [ProductController::class, 'saveItem'],
];
  /*
  'catalog' => [
    'type' => 'literal',
    'route' => '/catalog',
    'weight' => 1,
    'defaults' => [
      'module' => 'Catalog',
    ],
    'children' => [
      'products' => [
        'type' => 'segment',
        'route' => [
          'route' => '/products/',
          'constraints' => [
            'controller' => ProductController::class,
            'id' => '[0-9]+',
          ]
        ],
        'weight' => 3,
        'defaults' => [
          'controller' => 'Products1',
        ],
      ],
      /*'route' => [
        'route' => '[/:controller][/:action][/:id]',
        'constraints' => [
          'module' => '[a-zA-Z]+',
          'controller' => '[a-zA-Z]+',
          'action' => '[a-zA-Z]+',
          'id' => '[0-9]+',
        ]
      ],
      'weight' => 3,
      'defaults' => [
        'controller' => 'Products1',
      ],
    ],
  ],
];*/