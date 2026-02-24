<?php

namespace Pipe\DModel;

use Pipe\DModel\DBAdapter\DBAdapter;
use Pipe\DModel\DBAdapter\Sql\Select;
use Pipe\DModel\DModel;

/**
 * @template TItem
 */
class DModelList implements \Iterator
{

	public bool $loaded = false;
	public DModel $prototype;
	/** @var DModel[] */
	protected array $data = [];

	/**
	 * @param TItem $prototype
	 */
	public function __construct($prototype)
	{
		$this->prototype = $prototype;
	}

	public function join($bond, $fields = [])
	{
		$join = new Join(get_class($this->prototype), $this->select());
		$join->join($bond, $fields);

		return $join;

		/*$config = $this->prototype::getConfig()['bonds'][$bond];

		foreach($fields as $field) {
			$cols[$bond . '-' . $field] = $field;
		}

		$this->select()
			->join([$bond => ($config['class'])::table()], sprintf('t.%s = %s.%s', key($config['bond']), $bond, current($config['bond'])), $cols);

		return $this;*/
	}

	protected Select $select;
	public function select()
	{
		if(isset($this->select)) {
			return $this->select;
		}

		return $this->select = self::db()->select(['t' => $this->prototype::table()], ['id']);
	}

	public function addProperty($name, $options = [])
	{
		$this->prototype->addProperty($name, $options);
		return $this;
	}

	public function isLoaded()
	{
		return $this->loaded;
	}

	public function load($strict = true): bool|static
	{
		if($this->loaded) return $this;

		while (true) {
			try {
				$result = $this->select()->run();
				break;
			} catch (\Exception $e) {
				$this->prototype->sqlErrorFixer($e->getMessage());
			}
		}

		$this->data = [];
		foreach ($result as $row) {
			$this->data[] = (clone $this->prototype)->fill($row);
		}
		$this->loaded = true;
		return $this;
	}

	public function set($name, $value)
	{
		foreach ($this->data as $dmodel) {
			$dmodel->set($name, $value);
		}

		return $this;
	}

	protected array $new = [];

	public function add($data)
	{
		if(is_array($data)) {
			$dmodel = (clone $this->prototype)->set($data)->save();
		} elseif($data instanceof DModel) {
			$dmodel = $data;
		} else {
			throw new \Exception('Data must be array or instance of DModel');
		}

		$this->data[] = $dmodel;

		return $dmodel;
	}

	public function save(): static
	{
		foreach ($this->data as $dmodel) {
			$dmodel->save();
		}

		return $this;
	}

	public function findId($field, $val)
	{
			foreach ($this as $item) {
				if($item->$field == $val) return $item->id;
			}
			return 0;
	}

	/**
	 * @param callable(DModel, array): string $compare function($dmodel, $row) { return $data['id'] == $item->get('test_id'); }
	 * @param array $data
	 * @param callable(array, array): array | null $create function($row) { return $row; }
	 * @param callable(array, array): array | null $update function($row) { return $row; }
	 * @param callable(DModel): string | null $onDelete function($dmodel) {}
	 * @return $this
	 */
	public function fullUpdate($compare, $data, $create = null, $update = null, $delete = null)
	{
		$on = [
			'create' => $create !== null ? $create : function (array $row) { return array_merge((array) $row['create'], (array) $row['update']); },
			'update' => $update !== null ? $update : function (array $row) { return (array) $row['update']; },
			'delete' => $delete !== null ? $delete : function (DModel $dmodel) { $dmodel->remove(); },
		];
		$this->load();

		/*$bond = function ($row, $dmodel) {
			if($row['bonds']) {
				foreach ($row['bonds'] as $bond => $bondOptions) {
					$dmodel->bond($bond)->asd($bondOptions['compare'], $bondOptions['data'], $bondOptions['on'] ?? []);
				}
			}
		};*/

		if($compare !== false) {
			foreach ($this->data as $mKey => $dmodel) {
				$toDelete = true;
				foreach ($data as $dKey => $row) {
					if ($compare($dmodel, $row)) {
						if($on['update'] !== false) {
							$dmodel->set($on['update']($row))->save();
						}
						//$bond($row, $dmodel);
						unset($data[$dKey]);
						$toDelete = false;
						break;
					}
				}

				if($on['delete'] !== false) {
					if ($toDelete && is_callable($on['delete'])) {
						$on['delete']($dmodel);
						unset($this->data[$mKey]);
					}
				}
			}
		}

		foreach ($data as $row) {
			$this->add(array_merge(
				$on['create'] !== false ? $on['create']($row) : [],
				$on['update'] !== false ? $on['update']($row) : [])
			);
			//$dmodel = $this->add($on['create']($row));
			//$bond($row, $dmodel);
		}

		return $this;
	}


	/*public function fullUpdate(array $data, callable $compare, callable $onUpdate = null, $onDelete = null): static
	{
		if($onUpdate === null) {
			$onUpdate = function($dmodel, $row) { return $row; };
		}

		if($onDelete === null) {
			$onDelete = function(DModel $dmodel) { $dmodel->remove(); };
		}

		foreach ($this->data as $mKey => $dmodel) {
			$toDelete = true;
			foreach ($data as $dKey => $row) {
				if ($compare($dmodel, $row)) {
					$dmodel->set($onUpdate(
						$dmodel ? array_filter($dmodel->toArray(), function ($var) { return $var !== null && $var !== ''; }) : [],
						$row
					))->save();
					unset($data[$dKey]);
					$toDelete = false;
					break;
				}
			}

			if($toDelete) {
				$onDelete($dmodel);
				unset($this->data[$mKey]);
			}
		}

		foreach ($data as $row) {
			$this->add($onUpdate([], $row));
		}

		$this->save();

		return $this;
	}*/

	public function pipe()
	{
		$this->load();
		return (new Pipe())->setSource($this->data);
	}

	public function remove()
	{
		return $this->pipe()->remove();
	}
	protected static function db(): DBAdapter
	{
		return DBAdapter::getInstance();
	}

	public function rewind()
	{
		$this->load();

		if ($this->data) {
			reset($this->data);
		}

		return $this;
	}

	/**
	 * @return TItem
	 */
	public function current()
	{
		return current($this->data);
	}

	public function key()
	{
		return key($this->data);
	}

	public function next()
	{
		next($this->data);
	}

	public function valid()
	{
		if ($this->data == null) {
			return false;
		}
		$key = key($this->data);
		return $key !== null && $key !== false;
	}

	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	public function offsetGet($offset)
	{
		$this->load();
		return $this->data[array_keys($this->data)[$offset]];
	}

	public function offsetSet($offset, $value) {}

	public function offsetUnset($offset) {}

	public function toArray()
	{
		$arr = [];
		foreach ($this as $dmodel) {
			$arr[] = $dmodel->toArray();
		}
		return $arr;
	}

	public function __dump()
	{
		$this->load();
		$dump = [];
		$dump['select'] = $this->select()->__dump();
		$dump['data']   = $this->data ?? 'Not loaded';
		return $dump;
	}

	public function count()
	{
		return count($this->data);
	}
}