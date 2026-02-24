<?php
use Test\Controller\TestController;

return [
  '/test/add' => [TestController::class, 'add'],
  '/test/get' => [TestController::class, 'get'],
  '/test/list' => [TestController::class, 'list'],
];

  /*
  'catalog' => [
    'type'   => Parser\Literal::class,
    'route'  => '/catalog',
    'weight' => 1,
    'defaults'  => [
      'module'  => 'Test',
    ],
    'children' => [
      'products' => [
        'type'   => Parser\Segment::class,
        'route'  => [
          'route' => '/catalog',
          'constraints' => [
            'module'      => '[a-zA-Z]+',
            'controller'  => '[a-zA-Z]+',
            'action'      => '[a-zA-Z]+',
            'id'          => '[0-9]+',
          ]
        ],
        'weight' => 3,
        'defaults'  => [
          'controller'  => 'Products1',
        ],
      ],

        'route'  => [
          'route' => '[/:controller][/:action][/:id]',
          'constraints' => [
            'module'      => '[a-zA-Z]+',
            'controller'  => '[a-zA-Z]+',
            'action'      => '[a-zA-Z]+',
            'id'          => '[0-9]+',
          ]
        ],
        'weight' => 3,
        'defaults'  => [
          'controller'  => 'Products1',
        ],
      ],
    ],
  ],
];*/