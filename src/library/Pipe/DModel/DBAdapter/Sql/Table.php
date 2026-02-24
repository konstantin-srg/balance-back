<?php

namespace Pipe\DModel\DBAdapter\Sql;

use Pipe\DModel\DBAdapter\TPdo;

class Table
{
	use TPdo;

	protected string $table;
	public function __construct($table)
	{
		$this->table = $table;
	}

	public function addColumn($column, $type)
	{
		$query =
			sprintf("ALTER TABLE public.%s\nADD %s %s NULL;", $this->table, $column, $type);

		$this->pdo()->prepare($query)->execute();
	}

	public function create($columns)
	{
		$query =
			sprintf("CREATE TABLE public.%s (\n".
				"id int GENERATED ALWAYS AS IDENTITY NOT NULL,\n", $this->table);

		foreach($columns as $name => $options) {
			$query .=
				sprintf("%s %s NULL DEFAULT NULL,\n", $name, $options['type']);
		}

		$query .=
			sprintf("CONSTRAINT %s_pk PRIMARY KEY (id)\n", $this->table).
			");";

		$this->pdo()->prepare($query)->execute();
	}

	public function truncate()
	{
		$query =
			sprintf("TRUNCATE %s;", $this->table);

		$this->pdo()->prepare($query)->execute();

	}
}