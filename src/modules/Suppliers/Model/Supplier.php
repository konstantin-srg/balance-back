<?php
namespace Suppliers\Model;

use Pipe\Db\Entity\Entity;

class Supplier extends Entity
{
  static public function getFactoryConfig()
  {
    return [
      'table' => 'wh_suppliers',
      'properties' => [
        'name' => [],
        'email' => [],
        'rand'  => [
          'virtual' => true,
          'filters' => [
            'get' => function($model, $val) {
              return rand(1000, 9999);
            }
          ],
        ],
      ],
    ];
  }
}