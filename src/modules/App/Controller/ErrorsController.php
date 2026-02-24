<?php
namespace App\Public\Controller;

use Pipe\Mvc\Controller\AController;

class ErrorsController extends AController {
  public function error404Action()
  {
    return ['test' => 'my test val'];
  }
}