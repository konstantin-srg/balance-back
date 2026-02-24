<?php
namespace Pipe\Mvc\Controller;

use Pipe\Mvc\Service\AService;

abstract class ATableController extends AController
{
  protected array $links = [
    'module'  => '',
    'service' => '',
    'model'   => '',
  ];

  public function __construct(array $params)
  {
    parent::__construct($params);

    $class = get_called_class();
    $moduleName = substr($class, 0, strpos($class, '\\'));
    $serviceName = str_replace('Controller', '', substr($class, strrpos($class, '\\') + 1));
    $modelName = rtrim($serviceName, 's');

    $this->links['module'] = $moduleName;
    $this->links['service'] = $serviceName;
    $this->links['model'] = $modelName;
  }

  public function getItem()
  {
    return $this->service()->getItem($this->param('id'));
  }

  public function getItems()
  {
    return $this->service()->getItems($this->param('filters'));
  }

  public function delItem()
  {
    return $this->service()->delItem($this->param('id'));
  }

  public function saveItem()
  {
    return $this->service()->saveItem($this->params());
  }

  public function model() {
    return new ($this->links['module'] . '\\Model\\' . $this->links['model']);
  }

  protected AService $service;
  public function service() {
    if(!isset($this->service)) {
      $this->service = new ($this->links['module'] . '\\Service\\' . $this->links['service'] . 'Service');
      $this->service->setModel($this->model());
      $this->service->setSm($this->sm());
    }
    return $this->service;
  }
}