<?php

namespace Pipe\DModel\DBAdapter;


use JetBrains\PhpStorm\ArrayShape;
use Pipe\DModel\DBAdapter\Sql\Delete;
use Pipe\DModel\DBAdapter\Sql\Insert;
use Pipe\DModel\DBAdapter\Sql\Query;
use Pipe\DModel\DBAdapter\Sql\Select;
use Pipe\DModel\DBAdapter\Sql\Table;
use Pipe\DModel\DBAdapter\Sql\Update;
use Pipe\Exception\Exception;
use Pipe\StdLib\Singleton;

class DBAdapter
{
	use Singleton;
	protected \PDO $db;

	public function setConnection(
		#[ArrayShape(['driver' => 'string', 'database' => 'string', 'hostname' => 'string', 'username' => 'string',
		'password' => 'string', 'port' => 'string'])]
	                              $conn
	) {
		$this->db = new \PDO(
			sprintf('%s:host=%s;port=%s;dbname=%s', $conn['driver'], $conn['host'], $conn['port'], $conn['database']),
			$conn['user'],
			$conn['pass']
		);
	}

	public function select($table, $columns = [])
	{
		return (new Select($table, $columns))->setPdo($this->db);
	}

	public function delete($table, $filters)
	{
		return (new Delete($table, $filters))->setPdo($this->db);
	}

	public function insert($table, $values = [])
	{
		return (new Insert($table, $values))->setPdo($this->db);
	}

	public function update($table, $values, $filters = [])
	{
		return (new Update($table, $values, $filters))->setPdo($this->db);
	}

	public function query($table, $values = [])
	{
		return (new Query($table, $values))->setPdo($this->db);
	}

	public function table($table)
	{
		return (new Table($table))->setPdo($this->db);
	}
}