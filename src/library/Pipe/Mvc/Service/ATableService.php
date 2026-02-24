<?php
namespace Pipe\Mvc\Service;

use Pipe\Db\Entity\Entity;
use Pipe\Mvc\Engine\ServiceManager;

class ATableService extends AService
{
  protected Entity $model;
  protected ServiceManager $sm;

  public function getItemById($id)
  {
  }

  public function getItem($id)
  {
    return $this->model()->id($id);
  }

  public function getItems($filters = [])
  {
    return $this->model()->getCollection();
  }

  public function delItem($id)
  {
    return $this->model()->id($id)->remove();
  }

  public function saveItem($data)
  {
    return $this->model()->set($data)->save();
  }

  public function setModel($model) {
    $this->model = $model;
    return $this;
  }

  public function model() {
    return clone $this->model;
  }

  public function setSm($sm) {
    $this->sm = $sm;
    return $this;
  }

  public function sm(): ServiceManager {
    return $this->sm;
  }
}