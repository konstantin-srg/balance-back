<?php

namespace Pipe\DModel\DBAdapter\Sql;

class Join
{
	const JOIN_INNER = 'inner';
	const JOIN_OUTER = 'outer';
	const JOIN_LEFT = 'left';
	const JOIN_RIGHT = 'right';
	const JOIN_RIGHT_OUTER = 'right outer';
	const JOIN_LEFT_OUTER  = 'left outer';

	public function __construct(
		public string $table,
		public string $column,
		public string $on,
		public string $type)
	{}

	public function build(): string
	{
		return '';
	}
}