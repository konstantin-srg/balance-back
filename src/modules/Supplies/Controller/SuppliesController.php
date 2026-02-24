<?php

namespace Supplies\Controller;

use Pipe\Mvc\Controller\AController;

class SuppliesController extends AController
{

  public function supplies()
  {
    $asd = [];

    for ($i = 0; $i < 100; $i++) {

      $asd[] =
        [
          'id' => 3,
          'product' => [
            'id' => 3,
            'name' => 'Product 1',
            'img' => 'https://cdn1.ozone.ru/s3/multimedia-u/6403625298.jpg',
            'size' => '40 мл',
            'color' => 'Желтый',
            'pack' => '6',
            'price' => 100,
            //'boxCapacity'  => 50,
          ],
          'sales' => 5,
          'stock' => 150,
          'cost' => 50,
          'quantity' => 6,
          'summary' => 20000,
        ];

      $asd[] =
        [
          'id' => 2,
          'product' => [
            'id' => 2,
            'name' => 'Морилка водная',
            'img' => 'https://cdn1.ozone.ru/s3/multimedia-j/6456275899.jpg',
            'size' => '40 мл',
            'color' => 'Орегон',
            'pack' => '6',
            'price' => 100,
            'boxCapacity' => 20,
          ],
          'sales' => 5,
          'stock' => 150,
          'cost' => 50,
          'quantity' => 6,
          'summary' => 20000,
        ];
      $asd[] =
        [
          'id' => 2,
          'product' =>
            [
              'id' => 2,
              'name' => 'Клей моделист',
              'img' => 'https://cdn1.ozone.ru/s3/multimedia-s/6403609816.jpg',
              'size' => '40 мл',
              'color' => '',
              'pack' => '6',
              'price' => 100,
              'boxCapacity' => 50,
            ],
          'sales' => 5,
          'stock' => 150,
          'cost' => 50,
          'quantity' => 6,
          'summary' => 20000,
        ];
    }
    return $asd;
  }
}