<?php

namespace Pipe\DModel\DBAdapter\Sql;

class Delete extends AbstractQuery
{
	protected $table = '';
	protected $values = [];
	public function __construct($table = [], $filters = []) {
		$this->table($table);
		$this->where($filters);
	}

	public function values(array $values) {
		$this->values = $values;
		return $this;
	}


	function build()
	{
		$table = sprintf('`%s` AS `%s`', current($this->table), key($this->table));

		$set = '';
		$data = [];

		foreach ($this->values as $column => $value) {
			$set .= $column . " = ?,\n";
			$data[] = $value;
		}

		$whereQuery = $this->buildWhere();

		$query =
			sprintf("DELETE FROM %s\n", $table).
			$whereQuery['query'];

		return [
			'query' => $query,
			'data'  => array_merge($data, $whereQuery['data']),
		];
	}

	public function run()
	{
		return parent::run()->fetchAll(\PDO::FETCH_ASSOC);
	}
}