<?php

namespace Pipe\DModel\DBAdapter\Sql;

class Insert extends AbstractQuery
{
	protected $table = '';
	protected $values = [];
	public function __construct($table = [], $values = '') {
		$this->table($table);
		$this->values($values);
	}

	public function values(array $values) {
		if (!is_array(current($values))) {
			$values = [$values];
		}

		$this->values = $values;
		return $this;
	}


	function build()
	{
		$table = sprintf('`%s`', current($this->table));

		$columns = '';
		$values = '';
		$data = [];

		foreach ($this->values as $key => $row) {
			$values .= '(';

			$firstVal = true;
			foreach ($row as $column => $value) {
				if($key === 0) {
					$columns .= ($firstVal ? '' : ', ') . '`' . $column . '`';
				}

				$values .= ($firstVal ? '' : ', ') . '?';
				$data[] = $value;
				$firstVal = false;
			}

			$values .= '),' . "\n";
		}
		$values = rtrim($values, ",\n");

		$query =
				sprintf("INSERT INTO %s (%s)\nVALUES\n%s", $table, ltrim($columns, ', '), $values);

		return [
			'query' => $query,
			'data'  => $data,
		];
	}

	public function run()
	{
		parent::run();
		return $this->pdo()->lastInsertId();
	}
}