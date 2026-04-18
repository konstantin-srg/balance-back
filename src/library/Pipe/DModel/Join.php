<?php

namespace Pipe\DModel;

use Pipe\DModel\DBAdapter\Sql\Select;

class Join
{
	//protected string $dmodelClass;
	protected Select $select;
	protected $path = [];
	protected array $lastJoin;

	public function __construct(string $dmodelClass, Select $select)
	{
		$this->path[] = [
			'alias' => '',
			'class' => $dmodelClass,
		];
		$this->select = $select;
	}

	public function select()
	{
		return $this->select;
	}

	public function up()
	{
		array_pop($this->path);
		if(!empty($this->path)) {
			throw new \Exception('You climbing too high');
		}
		return $this;
	}

	public function down()
	{
		$this->path[] = $this->lastJoin;
		return $this;
	}

	public function join($bond, $fields = [], $where = [], $sum = false)
	{
		$parent = end($this->path);

		$config = ($parent['class'])::getConfig()['bonds'][$bond];

		if(!$config) {
			throw new \Exception('Unknown bond name in "' . $parent['class'] . '"');
		}

		$this->lastJoin = [
			'alias' => $bond,
			'class' => $config['class'],
		];

		if(count($this->path) < 2) {
			$tblAlias = $bond;
		} else {
			$tblAlias = '';
			for ($i = 1; $i < count($this->path); $i++) {
				$tblAlias .= $this->path[$i]['alias'] . '-';
			}
			$tblAlias .= $bond;
		}

		foreach($fields as $field) {
			$cols[$tblAlias . '-' . $field] = $field;
		}
		if(!$sum) {
			$cols[$tblAlias . '-id'] = 'id';
		}

		$main = 't';
		if(count($this->path) > 1) {
			$main = $this->path[count($this->path) - 1]['alias'];
		}

		foreach ($where as $expression => $val) {
			$this->select->where([$tblAlias . "." . $expression => $val]);
		}

		$this->select->join([$bond => ($config['class'])::table()], sprintf('`%s`.`%s` = `%s`.`%s`', $main, key($config['bond']), $bond, current($config['bond'])), $cols);

		return $this;
	}
}