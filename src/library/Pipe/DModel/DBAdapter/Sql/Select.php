<?php

namespace Pipe\DModel\DBAdapter\Sql;

use JetBrains\PhpStorm\ExpectedValues;

class Select extends AbstractQuery
{
	protected ?array $columns = null;
	protected $group = [];
	protected array $order = [];
	protected ?array $join = null;
	protected int $limit = 0;
	protected int $offset = 0;

	public function __construct($table = [], $columns = []) {
		$this->table($table);
		$this->columns($columns);
	}

	public function or() { $this->where[] = 'or'; return $this; }
	public function and() { $this->where[] = 'and'; return $this; }
	public function nest() { $this->where[] = 'nest'; return $this; }
	public function unnest() { $this->where[] = 'unnest'; return $this; }


	public function has(#[ExpectedValues(['order', 'join', 'columns'])] $element)
	{
		switch ($element) {
			case 'join': return !empty($this->join);
			case 'order': return !empty($this->order);
			case 'columns': return !empty($this->columns);
			default: throw new \Exception("Unknown element '$element'");
		}
	}

	public function table($table) {
		$this->table = is_string($table) ? ['t' => $table] : $table;
		return $this;
	}

	public function columns(array $columns = null) {
		if($columns === null) { return (array) $this->columns; }
		$this->columns = array_merge((array) $this->columns, $columns);
		return $this;
	}

	public function order(array | string $order) {
		$this->order = is_string($order) ? [$order => 'ASC'] : $order;
		return $this;
	}

	public function group(array | string $group) {
		$this->group = $group;
		return $this;
	}

	public function limit($limit, $offset = 0) {
		$this->limit  = $limit;
		$this->offset = $offset;
		return $this;
	}

	public function join($table, $on, $columns = [], $joinType = 'left') {
		$this->join[] =  ['table' => $table, 'on' => $on, 'columns' => $columns, 'type' => $joinType];
		return $this;
	}

	public function build()
	{
		$p = key($this->table);
		$table = sprintf('`%s` AS `%s`', current($this->table), $p);

		$ngc = [];
		$columnsSql = function ($columnsList, $prefix = null) use (&$ngc) {
			$sql = '';

			if($columnsList === null) return '';
			if(empty($columnsList))   return $prefix ? sprintf('`%s`.*', $prefix) : '';

			foreach ($columnsList as $colAlias => $col) {
				if(stripos($col, 'SUM(') !== false || stripos($col, 'COUNT(') !== false) {
					$sql .= sprintf('%s AS `%s`',  $col, $colAlias) . ', ';
				} else {
					$colName = $prefix ? sprintf('`%s`.`%s`', $prefix, $col) : '"' . $col . '"';
					$ngc[] = $colName;
					$sql .= (is_string($colAlias) ? sprintf('%s AS `%s`', $colName, $colAlias) : $colName) . ', ';
				}
			}
			return $sql;
		};
		$columns = $columnsSql($this->columns, $p);

		$joins = '';
		foreach ($this->join as $joinData) {
			switch ($joinData['type']) {
				case 'inner':
					$join = 'INNER JOIN '; break;
				case 'left':
					$join = 'LEFT JOIN '; break;
				case 'right':
					$join = 'RIGHT JOIN '; break;
				case 'full':
					$join = 'FULL JOIN '; break;
			}

			if(is_array($joinData['table'])) {
				$jp = key($joinData['table']);
				$jTable = sprintf('`%s` AS `%s` ', current($joinData['table']), $jp) ;
			} else {
				$jTable = $jp = $joinData['table'];
			}
			$join .= $jTable . ' ON ' . $joinData['on'] . "\n";

			$columns .= $columnsSql($joinData['columns'], $jp);
			$joins .= ' ' . $join ;
		}

		$whereQuery = $this->buildWhere();

		$order = '';
		foreach ($this->order as $col => $direction) {
			$order .= sprintf('%s %s, ', $col, $direction);
		}
		if($order) $order = 'ORDER BY ' . rtrim($order, ', ') . "\n";

		$group = '';
		if($this->group == 'auto') {
			$group = implode(', ', $ngc);
		} else {
			foreach ($this->group as $col) {
				$group .= str_contains('.', $col) ? sprintf('`%s`, ', $col) : sprintf('`%s`.`%s`, ', $p, $col);
			}
		}
		if($group) $group = 'GROUP BY ' . rtrim($group, ', ') . "\n";

		$query =
			rtrim(
				sprintf("SELECT %s\nFROM %s\n", rtrim($columns, ', '), $table).
				$joins.
				$whereQuery['query'].
				$group.
				$order
			) . ';';

		return [
			'query' => $query,
			'data'  => $whereQuery['data'],
		];
	}

	public function run()
	{
		return parent::run()->fetchAll(\PDO::FETCH_ASSOC);
	}
}