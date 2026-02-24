<?php
namespace Pipe\Mvc\Engine;

use Pipe\StdLib\IConfigurable;
use Pipe\StdLib\ISingleton;
use Pipe\StdLib\Singleton;

class ServiceManager
{
  protected $_binds  = [];
  protected $_config = [];
	protected array $_instances = [];

  use Singleton;

  /*public function add($alias, $config)
  {
    $this->_config[$alias] = $config;
  }*/

  public function setConfig(array $config)
  {
    $this->_config = &$config;

    foreach ($config as $name => &$item) {
      if(is_string($item)) {
        $item = ['class' => $item];
        continue;
      }

      if(isset($item['binds'])) {
	      $this->_binds[$item['binds']] = $name;

				/*d($item['binds']);
        foreach ((array) $item['binds'] as $interface) {
          $this->_binds[$interface] = $name;
        }*/

        unset($item['binds']);
      }
    }
  }

  public function get($alias)
  {
	  $alias = ucfirst($alias);

	  if(isset($this->_instances[$alias])) {
		  return $this->_instances[$alias];
	  }

	  $this->_instances[$alias] = $this->construct($alias);

    return $this->construct($alias);
  }

	public function __call($name, $arguments)
	{
		return $this->get($name);
	}

  private function construct($alias)
  {
    $config = $this->_config[$alias] ?? [] + [
      'config'      => [],
    ];

    if(!isset($this->_config[$alias]) || !($class = $config['class'])) {
      $class = $alias;
    }

    $interfaces = (array) class_implements($class);

    $objConfig = [];
    foreach ($interfaces as $interface) {
      if(isset($this->_binds[$interface])) {
        $objConfig[strtolower($this->_binds[$interface])] = $this->get($this->_binds[$interface]);
      }
    }

    if(isset($config['object'])) {
	    $obj = $config['object'];
    } elseif(isset($config['factory'])) {
      $obj = call_user_func_array($config['factory'], [$this]);
    } else {
      $obj = in_array(ISingleton::class, $interfaces) ? $class::getInstance($objConfig) : new $class($objConfig);
    }

    return $obj;
  }

	public function __dump() { return 'too big'; }
}