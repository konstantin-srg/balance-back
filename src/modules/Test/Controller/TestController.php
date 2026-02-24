<?php
namespace Test\Controller;

use Pipe\Mvc\Controller\AController;

class TestController extends AController {
  public function add()
  {
    return [
      'id'  => 123,
      'name' => 'qweqweq',
    ];

    $options = array(
      'key'		=>	'value',
    );

    /*$ch = curl_init('http://pipeline/test/get');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $options);
    $result = curl_exec($ch);
    curl_close($ch);
    dd($result);
    $result = explode("\n", $result);

    if ($params) {
      $postParams['json'] = $params;
    }

    $response = $client->post($url, $postParams);

    return Json::decode($response->getBody()->getContents(), 1);*/
  }

  public function list()
  {
    //dd($this->params());
    return [
      [
        'id'    => 1,
        'title' => 'Product 1',
        'desc'  => 'Description of product 1',
        'price' => 100,
      ],
      [
        'id'    => 2,
        'title' => 'Product 2',
        'desc'  => 'Description of product 2',
        'price' => 250,
      ],
      [
        'id'    => 3,
        'title' => 'Product 3',
        'desc'  => 'Description of product 3',
        'price' => 200,
      ],
    ];
  }

  public function get()
  {
    return $_POST;
    return ['test' => 'list'];
  }
}