<?php
use App\Controller\IndexController;

return [
  '/404' => [IndexController::class, 'error404'],
];