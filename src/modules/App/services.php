<?php

return [
	'Db'  => [
		'factory' => function() {
			return new Laminas\Db\Adapter\Adapter([
				'driver'   => 'Pdo_Pgsql',
				'hostname' => 'PostgreSQL-17',
				'database' => 'balance',
				'username' => 'postgres',
				'password' => '',
				'charset'  => 'UTF-8',
				'port'     => '5432',
			]);
		}
	],
];