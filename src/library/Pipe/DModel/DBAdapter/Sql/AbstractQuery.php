<?php

namespace Pipe\DModel\DBAdapter\Sql;

use Pipe\DModel\DBAdapter\TPdo;

class AbstractQuery
{
	use TPdo;
	protected $table;
	protected $where = [];

	public function table($table) {
		$this->table = is_string($table) ? ['t' => $table] : $table;
		return $this;
	}

	public function where($where, $values = []) {
		if (is_callable($where)) {
			$where($this);
			return $this;
		}

		if(is_string($where)) {
			$this->where[] = [
				'expression' => $where,
				'value'      => $values,
			];
			return $this;
		}

		foreach ($where as $expression => $value) {
			if(is_bool($value)) $value = $value ? 'TRUE' : 'FALSE';
			if($value == Where::SKIP) continue;

			if(!str_contains($expression, ' ')) {
				if(str_contains($expression, '.')) {
					$expression = sprintf('`%s` = ?', $expression);
				} else {
					$expression = sprintf('`%s`.`%s` = ?', key($this->table), $expression);
				}
			}

			$this->where[] = [
				'expression' => $expression,
				'value'      => $value,
			];
		}

		return $this;
	}

	protected function buildWhere() {
		$where = '';
		$gluing = false;
		$data = [];
		foreach ($this->where as $whereData) {
			if(is_string($whereData)) {
				switch($whereData) {
					case 'and':
						$where .= "\nAND\n";
						$gluing = false; break;
					case 'or':
						$where .= "\nOR\n";
						$gluing = false; break;
					case 'nest':
						$where .= ($gluing ? "\nAND\n" : '') . ' (';
						$gluing = false; break;
					case 'unnest':
						$where .= ')';
						$gluing = true; break;
					default:
						throw new \Exception('Unexpected SQL expression data: ' . $whereData);
				}
				continue;
			}

			if(isset($whereData['value'])) {
				$data[] = is_bool($whereData['value']) ? (int) $whereData['value'] : $whereData['value'] ;
			}

			$where .= ($gluing ? "\nAND\n" : '') . str_replace('%s', '?', $whereData['expression']);

			$gluing = true;
		}

		$where =
			($where ? "WHERE\n" . $where . "\n" : '');

		return [
			'query' => $where,
			'data'  => $data,
		];
	}

	function __dump()
	{
		$data = $this->build();
		return vsprintf(str_replace('?', '%s', $data['query']), $data['data']);
	}


	public function run()
	{
		$queryData = $this->build();
		$sql = $queryData['query'];
		$params = $queryData['data'];

		try {
			$p = $this->pdo()->prepare($sql);
			$p->execute($params);

		} catch (\Exception$e) {
			$msg = $e->getMessage();
			$msg = str_replace(" ERROR", "\nERROR", $msg);

			$msg .= "\n\n". $this->__dump();

			throw new \Exception($msg);
		}

		return $p;
	}
}