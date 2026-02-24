<?php

namespace App;

use Pipe\DModel\DBAdapter\DBAdapter;
use Pipe\Mvc\Engine\AbstractBootstrap;

class Bootstrap extends AbstractBootstrap {
	public function onInit() {
		DBAdapter::getInstance()->setConnection([
			'driver'   => 'mysql',
			'host'     => 'db',
			'port'     => '3306',
			'user'     => 'root',
			'pass'     => 'asdqwezxc',
			'database' => 'balance',
		]);
	}
}