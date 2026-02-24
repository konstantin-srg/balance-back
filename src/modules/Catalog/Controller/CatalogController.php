<?php
namespace Catalog\Controller;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Pipe\DModeler\Old\DModeler;
use Pipe\Mvc\Controller\AController;

class CatalogController extends AController {
  public function products()
  {
    return [
      [
        'id' => 1,
        'name' => 'Product 1',
        'img' => 'https://cdn1.ozone.ru/s3/multimedia-u/6403625298.jpg',
        'size' => '40 мл',
        'color' => 'Желтый',
        'amount' => '6',
        'price' => 100,
      ],
      [
        'id' => 1,
        'name' => 'Морилка водная',
        'img' => 'https://cdn1.ozone.ru/s3/multimedia-j/6456275899.jpg',
        'size' => '40 мл',
        'color' => 'Орегон',
        'amount' => '6',
        'price' => 100,
      ],
      [
        'id' => 1,
        'name' => 'Клей моделист',
        'img' => 'https://cdn1.ozone.ru/s3/multimedia-s/6403609816.jpg',
        'size' => '40 мл',
        'color' => '',
        'pack' => '0',
        'price' => 100,
      ],
    ];
  }

  public function list()
  {
    $db = [
      'driver'   => 'Pdo_Mysql',
      'hostname' => 'localhost',
      'database' => 'balance',
      'username' => 'root',
      'password' => '',
      //'charset'  => 'utf8',
      'port'     => '3306',
  ];

    $dm = DModeler::getInstance();
    $dm->setSql(new Sql(new Adapter($db)));

    $dm->get('product');

    return ['catalog' => 'list'];
  }
}