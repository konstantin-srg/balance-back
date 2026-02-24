<?php
namespace Catalog\Controller;

use Catalog\Model\Products;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Pipe\DModeler\Old\DModeler;
use Pipe\Mvc\Controller\AController;

class ProductController extends AController
{
  public function getItem()
  {
    return new Products($this->param('id'));
  }

  public function getItems()
  {
    $items = Products::getEntityCollection();
    return $items;
  }

  public function saveItem()
  {
    dd($this->param());
    $items = Products::getEntityCollection();
    return $items;
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

    dd();
    return ['catalog' => 'list'];
  }
}