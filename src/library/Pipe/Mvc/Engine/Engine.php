<?php
namespace Pipe\Mvc\Engine;

use Composer\Autoload\ClassLoader;
use JetBrains\PhpStorm\NoReturn;
use Laminas\Json\Json;
use Pipe\File\Director;
use Pipe\Mvc\Controller\AController;
use Pipe\StdLib\TSingleton;

class Engine
{
  use TSingleton;

  protected string $cachePrefix = 'donkey';
  protected array $dirs    = [];

  protected ClassLoader $composer;
  protected ServiceManager $sm;
  public function setConfig($options)
  {
    $this->composer  = $options['composer'];
    $this->dirs = [
      'root_dir'     => $options['baseDir'],
      'modules_dir'  => $options['baseDir'] . '/modules',
      'modules'      => [],
    ];

    return $this;
  }

  public function start()
  {
	  header('Access-Control-Allow-Origin: *');
	  header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
	  header('Access-Control-Allow-Headers: append,delete,entries,foreach,get,has,keys,set,values,Authorization, Content-Type, X-Requested-With');
	  header('Access-Control-Allow-Headers: Content-Type');
	  header('Access-Control-Allow-Credentials: true');


		//header('Content-type: application/json');
	  /*header('Access-Control-Allow-Origin: *');
	  header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
	  header('Access-Control-Allow-Headers: append,delete,entries,foreach,get,has,keys,set,values,Authorization, Content-Type, X-Requested-With');*/

    $this->initDirs();
	  $this->bootstrap();
	  $this->initSm();
    $route = $this->parseRoute();

    /*try {
      $body = file_get_contents('php://stdin');
      $request = $body ? json_decode($body) : [];
    } catch (\Exception) {
      $this->send([
        'status' => false,
        'error'  => 'Request not recognized as JSON',
      ]);
    }

    $this->send((new $route['controller']($request))->$action($params));*/

    $route = $this->parseRoute();
    /** @var AController $controller */
    $controller = new $route['controller']($route['params']);
    $controller->setSm($this->sm);
    //$controller->setDm($this->dm);
    $this->send($controller->{$route['action']}());
  }

  #[NoReturn] protected function send($data)
  {
    if (is_object($data)) {
      $data = $data->toArray();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' || ($_SERVER['HTTP_REFERER'] && $_SERVER['HTTP_REFERER'] != $_SERVER['HTTP_HOST'])) {
      header('Content-Type: application/json; charset=utf-8');

      die(json_encode($data));
    }

    dd($data);
  }

//  public function initDm()
//  {
//	  $dmConfig = [];
//	  (new Director())->scan($this->dirs['modules_dir'] . '/**/dm.php')
//		  ->each(function ($file) use (&$dmConfig) {
//			  $dmConfig += include($file->path());
//		  });
//
//		Links::getInstance()->addLink($dmConfig);
//  }

	/**
	 * @return string
	 */
	public function bootstrap()
	{
		foreach($this->dirs['modules'] as $module => $moduleDir) {
			$path = $moduleDir . '/bootstrap.php';
			if(file_exists($path)) {
				require_once $path;
				$class = $module . '\Bootstrap';
				$bt = (new $class())->onInit();
			}
		}
	}

  public function initSm()
  {
    $sm = ServiceManager::getInstance();

	  $config = [
			/*'Dm' => [
				'object' => DModeler::getInstance(),
				'binds'  => IDmAware::class,
			],*/
		  'Sm' => [
			  'object' => $sm,
			  'binds' => ISmAware::class,
		  ],
	  ];

	  foreach (scandir($this->dirs['modules_dir']) as $key => $module) {
			if($module === '.' || $module === '..') continue;

			$serviceDir = $this->dirs['modules_dir'] . '/' . $module . '/service';
			if(!is_dir($serviceDir)) continue;

		  foreach (scandir($this->dirs['modules_dir'] . '/' . $module . '/Service') as $key => $service) {
			  if($service === '.' || $service === '..') continue;

			  $serviceClass = $module . '\\Service\\' . str_replace('.php', '', $service);

			  $config[$serviceClass] = $serviceClass;
		  }
	  }

    (new Director())->scan($this->dirs['root_dir'] . '/config')
      ->each(function ($file) use (&$config) {
        $config += include($file->path());
      });

    $sm->setConfig($config);
    $this->sm = $sm;
  }

  public function sm(): ServiceManager
  {
    return $this->sm;
  }

  protected function parseRoute()
  {
    $routesConfig = [];

    (new Director())->scan($this->dirs['modules_dir'] . '/*/routes.php')
      ->each(function ($file) use (&$routesConfig) {
        $routesConfig += include($file->path());
      });

    $url = rtrim($_SERVER['REQUEST_URI'], '/');
    if(strpos($url, '?')) {
      list($url, $paramsStr) = explode('?', $url);
    }

    if(!isset($routesConfig[$url])) {
      header('HTTP/1.1 404 Not found');
      $route = $routesConfig['/404'];
      $params = [];
    } else {
      header('HTTP/1.1 200 OK');
      $route = $routesConfig[$url];
    }

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			if($_GET['json']) {
				$params = Json::decode($_GET['json'], Json::TYPE_ARRAY);
			} else {
				$params = $_GET;
			}
		} else {
			$params = array_merge($_POST, Json::decode(file_get_contents('php://input'), Json::TYPE_ARRAY));
		}

    return [
      'controller'  => $route[0],
      'action'      => $route[1],
      'params'      => $params,
    ];
  }

  protected function initDirs(): static
  {
    $dirSep = DIRECTORY_SEPARATOR;
    if ($dh = opendir($this->dirs['modules_dir'])) {
      while (($moduleDir = readdir($dh)) !== false) {
        if(filetype($this->dirs['modules_dir'] . $dirSep . $moduleDir) == 'dir' && !in_array($moduleDir, ['.', '..'])) {
          $this->dirs['modules'][$moduleDir] = $this->dirs['modules_dir'] . $dirSep . $moduleDir;
          $this->composer->addPsr4($moduleDir . '\\', $this->dirs['modules_dir'] . $dirSep . $moduleDir . '/');
        }
      }
    }
    return $this;
  }
}