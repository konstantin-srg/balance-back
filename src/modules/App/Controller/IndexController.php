<?php
namespace App\Controller;

use Pipe\Mvc\Controller\AController;

class IndexController extends AController {

  public function indexAction()
  {
    die('APP | IndexController');
  }

  public function test()
  {
      die('APP | testAction');
  }

  public function error404()
  {
    return ['errors' => 'route not found'];
  }
}