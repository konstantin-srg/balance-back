<?php

namespace Pipe\DModel\DBAdapter\Sql;

class Query extends AbstractQuery
{
	protected $query = '';
	protected $params = [];
	public function __construct($query = '', $params = []) {
		$this->query = $query;
		$this->params = $params;
	}

	public function build()
	{
		return [
			'query' => $this->query,
			'data'  => $this->params,
		];
	}
}