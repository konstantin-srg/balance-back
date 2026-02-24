<?php

namespace Pipe\DModel;

use JetBrains\PhpStorm\ArrayShape;
use Pipe\DModel\Containers\AbstractContainer;
use Pipe\DModel\Containers\ContainerFactory;
use Pipe\DModel\DBAdapter\DBAdapter;
use Pipe\DModel\DBAdapter\Sql\Select;
use Pipe\Exception\Exception;

/**
 * @template T
 * @property integer $id
 */
class DModel implements \Iterator
{
	protected ?int $id = null;
	protected array $properties = [];

	protected Select $select;
	public function select()
	{
		if(isset($this->select)) {
			return $this->select;
		}

		return $this->select = self::db()->select(['t' => static::table()]);
	}

	public function __construct() {}
	static public function truncate()
	{
		self::db()->table(static::table())->truncate();
	}

	protected static function db(): DBAdapter
	{
		return DBAdapter::getInstance();
	}

	////////////////////////////////////////////////////
	//STATIC
	////////////////////////////////////////////////////

	static public function find(array | string $fields = [], string | int | array $filters = [], callable | string $check = null): static | bool
	{
		$model = (new static());

		if($filters['id']) {
			$model->id($filters['id']);
			unset($filters['id']);
		}

		$model->select()->columns($fields)->where($filters);

		if($check == 'not empty' && $model === false) {
			$msg = sprintf("Can't find row in table \"%s\"\n", static::table());
			if(is_array($filters)) {
				$msg .= "Filters:\n";
				foreach ($filters as $key => $value) {
					$msg = sprintf("%s = %s\n", $key, $value);
				}
			}
			throw new \Exception($msg);
		} elseif($check instanceof \Closure) {
			$check($model);
		}

		return $model->load();
	}

	static public function create($values, $save = true): static
	{
		$dmodel = new static();

		foreach (self::getConfig()['properties'] as $name => $opts) {
			if(!isset($values[$name]) && $opts['default']) {
				$values[$name] = $opts['default'];
			}
		}

		try {
			$dmodel->set($values);
		} catch (\Exception $e) {
			$msg = $e->getMessage() . '. ' .
				'Can`t create model with data: ' . var_export($values, true);
			throw new \Exception($msg);
		}

		$dmodel->loaded = true;
		return $save ? $dmodel->save() : $dmodel;
	}

	static public function findOrCreate($fields = [], $filters = [], $values = []): static
	{
		$values = array_merge($filters, $values);
		return self::find($fields, $filters) ?: self::create($values, false);
	}

	/**
	 * @return DModelList<static>
	 */
	static public function list($fields = null, $filters = [], $load = false)
	{
		if($fields === null) {
			$fields = array_keys(self::getConfig()['properties']);
		}
		$list = new DModelList(new static());
		$list->select()
			->columns($fields)
			->where($filters);

		return $load ? $list->load() : $list;
	}

	////////////////////////////////////////////////////
	//CRUD
	////////////////////////////////////////////////////\

	protected $loaded = false;

	public function isLoaded()
	{
		return $this->loaded;
	}

	/**
	 * @param array|string $fields
	 * @param array|callable(Select):void $filters
	 * @return $this|false
	 * @throws \Exception
	 */
	protected function load(array | string $fields = [], array | callable $filters = [])
	{
		if($this->id || $this->loaded) {
			return $this;
		}

		$select = $this->select();

		if(!empty($select->columns())) {
			$select->columns(['id']);
		}

		try {
			$result = $select->run();
		} catch (\Exception $e) {
			d(get_called_class());
			d('Error');
			dd($select);
			$this->sqlErrorFixer($e->getMessage());
		}

		if(empty($result)) {
			return false;
		}

		if(count($result) > 1) {
			throw new \Exception(get_called_class() . ': Find more than one result: ' . $select->__dump());
		}

		$this->fill($result[0]);
		$this->id($result['id']);

		return $this;
	}

	public function save()
	{
		/*if(get_called_class() == 'Ozon\Model\OzonProductsSku') {
			throw new \Exception('asdad');
			dd($this->properties);
		}*/

		$data = [];
		foreach ($this->properties as $name => $propOpts) {
			/** @var AbstractContainer $container */
			$container = $propOpts['container'];

			if ($this->id() !== 0 && !$container->isChanged()) continue;

			$container->save();
			$container->isChanged(false);
			$data[$name] = $container->serialize();
		}

		if(!$data) {
			return $this;
		}

		$isUpdate = $this->id();

//		while (true) {
//			try {
		if($isUpdate) {
			self::db()->update(self::table(), $data, ['id' => $this->id()])->run();
		} else {
			$this->id(self::db()->insert(self::table(), $data)->run());
		}
//				break;
//			} catch (\Exception $e) {
//				$this->sqlErrorFixer($e->getMessage());
//			}
//			break;
//		}

		foreach ($this->bonds as $bond) {
			//$bond['obj']->save();
		}

		/*foreach (static::getConfig()['bonds'] as $name => $options) {
			if($options['onUpdate'] === null) continue;
			if($options['onCreate'] === null) continue;
		}*/

		return $this;
	}

	public function remove()
	{
		foreach (self::getConfig()['bonds'] as $name => $opts) {
			if ($opts['onDelete'] === 'remove') {
				$this->bond($name)->remove();
			}
		}

		if ($this->id())
			self::db()->delete(self::table(), ['id' => $this->id()])->run();
	}

	public function sqlErrorFixer($error)
	{
		$msg = preg_replace('/[ ]{2,}|[\t]|[\r\n]|[\n]/', ' ', strtolower($error));

		/*if (str_contains($msg, 'sqlstate[42p01]')) {
			$this->createTable();
			return;
		}

		if (str_contains($msg, 'sqlstate[42703]')) {
			preg_match('/error: column (.+) does not exist/ms', $msg, $matches);

			if (!isset($matches[1])) {
				throw new \Exception("Can't parse column name");
			}

			$colName = substr($matches[1], strpos($matches[1], '.') + 1);

			if (!array_key_exists($colName, self::getConfig()['properties'])) {
				throw new \Exception('Unknown column: ' . $colName);
			}

			$this->addColumn($colName, $this->getProperty($colName)['container']::SQL_TYPE);
			return;
		}*/

		throw new \Exception($error);
	}

	protected function addColumn($column, $type)
	{
		self::db()->table(static::table())->addColumn($column, $type);
	}

	protected function createTable()
	{
		$columns = [];
		foreach(self::getConfig()['properties'] as $name => $options) {
			$columns[$name] = [
				'type' => ContainerFactory::containerFactory($options['type'])::SQL_TYPE,
			];
		}
		self::db()->table(static::table())->create($columns);
	}

	static public function table()
	{
		return self::getConfig()['table'];
	}

	public function id($id = null)
	{
		if ($id === null) { return $this->id; }

		$this->id = (int) $id;

		return $this;
	}

	/*protected function isLoaded($isLoaded = null)
	{
		if($isLoaded !== null) { $this->loaded = $isLoaded; return $this; }
		return $this->loaded;
	}*/

	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

	public function set($name, $value = null, $options = []): static
	{
		if (is_array($name)) {
			foreach ($name as $key => $val) {
				$this->set($key, $val, $value);
			}

			return $this;
		}

		if ($name === 'id') {
			return $this->id($value);
		}

		if(str_contains($name, '()')) {
			$bond = strstr($name, '()', true);
			$name = substr($name, strlen($bond) + 3);

			$bond = $this->bond($bond);
			if ($bond instanceof DModelList) {
				foreach ($value as $row) {
					$bond->add($row);
				}
			} else {
				$bond->set($name, $value);
			}

			return $this;
		}

		$container = $this->getProperty($name)['container'];
		$container->set($value);

		/*if (isset($options['changed'])) {
			$container->isChanged($options['changed']);
		}*/

		return $this;
	}


	public function __get($name)
	{
		return $this->get($name);
	}

	public function get(string $name, $options = null)
	{
		if ($name === 'id') { return $this->id(); }

		$this->load();

		return $this->getProperty($name)['container']->get($options);
	}

	public function __clone()
	{
		foreach ($this->properties as $name => $propOpts) {
			$this->properties[$name]['container'] = clone $propOpts['container'];
		}
		$this->id = null;
	}

	public function clear()
	{
		foreach ($this->properties as $name => $propOpts) {
			$this->properties[$name]['container'] = (clone $propOpts['container'])->clear();
		}
		$this->id = null;
	}

	////////////////////////////////////////////////////
	//Links
	////////////////////////////////////////////////////

	/*protected $links = [];
	public function getParent($alias = null)
	{
		if($alias === null) { $alias = get_called_class(); }
		return $this->links()->getParent($alias);
	}

	public function getChildren($alias = null)
	{
		if($alias === null) { $alias = get_called_class(); }
		return $this->links()->getChildren($alias);
	}*/

	////////////////////////////////////////////////////
	//Properties
	////////////////////////////////////////////////////
//#[ArrayShape([
//'virtual'   => 'boolean',
//'container' => AbstractContainer::class,
//]);

	#[ArrayShape([
		'virtual'   => 'boolean',
		'container' => AbstractContainer::class,
	])]
	protected function getProperty($name): array
	{
		if ($this->properties[$name] === null) {
			$this->addProperty($name);
		}

		return $this->properties[$name];
	}

	public function addProperty($name, $options = null): static
	{
		if (is_array($name)) {
			foreach ($name as $key => $val) {
				is_int($key) ? $this->addProperty($val) : $this->addProperty($key, $val);
			}
			return $this;
		}

		if(is_string($options)) {
			$options = ['type' => $options];
		}

		if($options === null) {
			if (!isset(self::getConfig()['properties'][$name])) {
				if($this->strict) {
					throw new \Exception('Property "' . $name . '" not defined in ' . get_called_class());
				}
				$containerObj = ContainerFactory::containerFactory('string');
			} else {
				$containerObj = ContainerFactory::containerFactory(self::getConfig()['properties'][$name]['type']);
			}
		} else {
			$containerObj = ContainerFactory::containerFactory($options['type']);
		}

		/*if (isset($options['options'])) {
			$containerObj->setOptions($options['options']);
		}*/

		$this->properties[$name] = [
			'virtual'   => false,
			'container' => $containerObj,
		];

		return $this;
	}

	public function fill($data)
	{
		if (!$data) {
			return false;
		}

		if(isset($data['id'])) {
			$this->id($data['id']);
			unset($data['id']);
		}

		$bondsData = [];
		foreach ($data as $name => $value) {
			if($value === null) continue;
			$sepPos = strpos($name, '-');
			if(!$sepPos) {
				$this->getProperty($name)['container']->unserialize($value);
				continue;
			}

			$bond = substr($name, 0, $sepPos);

			if (!isset($bondsData[$bond])) {
				$bondsData[$bond] = [];
			}

			$key = substr($name, $sepPos + 1);
			$bondsData[$bond][$key] = $value;
		}

		/*foreach ($data as $name => $value) {
			$this->getProperty($name)['container']->unserialize($value);
		}*/

		foreach ($bondsData as $bond => $data) {
			$this->bond($bond)->fill($data);
		}

		$this->loaded = true;

		return $this;
	}

	public function has($name)
	{
		return array_key_exists($name, self::getConfig()['properties']) || $name == 'id';
	}

	////////////////////////////////////////////////////
	// Config
	////////////////////////////////////////////////////
	static protected function setConfig()
	{
		return [];
	}

	public function __call($name, $arguments)
	{
		$fields  = $arguments[0] ?? [];
		$filters = $arguments[1] ?? [];
		$default = $arguments[2] ?? [];

		return $this->bond($name, $fields, $filters, $default);
	}

	#[ArrayShape(['class' => DModel::class | DModelList::class])]
	protected $bonds = [];
	public function bond($name, $fields = [], $filters = [], $default = [], $type = 'auto'): DModelList | DModel
	{
		if($this->bonds[$name]) {
			if(!$this->bonds[$name]['init']) $this->initBond($name);

			return $this->bonds[$name]['obj'];
		}

		if(!isset(self::getConfig()['bonds'][$name]))
			throw new \Exception('Bond ' . $name . ' not defined in ' . get_called_class());

		$options = self::getConfig()['bonds'][$name];

		/*if(!($parentId = $this->get(key($options['bond'])))) {
			throw new \Exception(sprintf("Can`t load bond %s because field %s in %s is NUll", $name, key($options['bond']), get_called_class()));
		}*/

		$type = $type !== 'auto' ? $type : $options['type'];

		switch ($type) {
			case 'otm':
				/** @var DModelList $bond */
				$bond = ($options['class'])::list($fields, $filters, false);
				break;
			case 'oto':
				/** @var DModel $child */
				$default[$options['field']] = $this->id();
				$bond = new ($options['class']);
				//$bond = ($options['class'])::findOrCreate($fields, $filters, $default);
				break;
			default:
				throw new \Exception(sprintf('Unknown relationship type %s for child %s in %s' . $options['type'], $name, get_called_class()));
		}

		$this->bonds[$name] = [
			'obj' => $bond,
			'int'   => false,
		];

		return $this->bond($name);
	}

	protected function initBond($name)
	{
		$options = self::getConfig()['bonds'][$name];

		if(!($parentId = $this->get(key($options['bond'])))) {
			return;
			//throw new \Exception(sprintf("Can`t load bond %s because field %s in %s is NUll", $name, key($options['bond']), get_called_class()));
		}

		$obj = $this->bonds[$name]['obj'];

		$obj->select()->where([current($options['bond']) => $parentId]);
		//$obj->load();

		if($obj instanceof DModelList) {
			$obj->prototype->set(current($options['bond']), $parentId);
		} else {
			$obj->set(current($options['bond']), $parentId);
		}

		$this->bonds[$name]['init'] = true;
	}

	static protected array $config = [];
	static public function getConfig(): array
	{
		if(self::$config[static::class]) {
			return self::$config[static::class];
		}

		$config = static::setConfig();

		self::$config[static::class] =
			[
				'table' => $config['table'],
				'properties' => [],
				'bonds'      => [],
			];

		//$pConf = &self::$config[static::class];

		foreach ($config['bonds'] as $name => $options) {
			self::$config[static::class]['bonds'][$name] = array_merge(
				[
					'class'     => null,
					'bond'      => ['parent_id' => 'id'],
					'type'      => 'otm',
					'onDelete'  => null,
					'onUpdate'  => null,
					'onCreate'  => null,
				],
				$options
			);
		}

		foreach ($config['properties'] as $name => $options) {
			if(is_string($options)) {
				$options = ['type' => $options];
			}

			self::$config[static::class]['properties'][$name] = array_merge(
				[
					'default'   => null,
					'type'      => 'string',
					'virtual'   => false,
					'container' => null,
					'filters'   => [],
				],
				$options
			);
		}

		return self::$config[static::class];
	}

	////////////////////////////////////////////////////
	// Iterator
	////////////////////////////////////////////////////
	public function rewind()
	{
		return reset($this->properties);
	}

	public function current(): mixed
	{
		$prop = current($this->properties);
		return $prop['container']->get();
	}

	public function key()
	{
		return key($this->properties);
	}

	public function next()
	{
		return next($this->properties);
	}

	public function valid()
	{
		$key = key($this->properties);
		return ($key !== null && $key !== false);
	}

	public function toArray(): array
	{
		$arr = ['id' => $this->id()];

		foreach ($this->properties as $name => $property) {
			$arr[$name] = $property['container']->get();
		}

		if($this->bonds) {
			foreach ($this->bonds as $name => $bond) {
				$arr['bonds'][] = $this->bonds->__dump();
			}
		}

		return $arr;
	}

	////////////////////////////////////////////////////
	// Dump
	////////////////////////////////////////////////////
	public function __dump($options = ['da' => true])
	{
		$props = [
			'id'     => $this->id,
		];

		foreach ($this->properties as $name => $property) {
			$props[$name] = $property['container']->__dump();
		}

		return $props;
	}
}