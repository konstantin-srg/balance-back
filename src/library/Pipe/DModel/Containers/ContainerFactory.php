<?php

namespace Pipe\DModel\Containers;

class ContainerFactory
{
	static function containerFactory($type): AbstractContainer
	{
		switch ($type) {
			case 'json':
				$containerObj = new Json();
				break;
			case 'integer':
			case 'int':
				$containerObj = new Num();
				break;
			case 'datetime':
			case 'timestamp':
				$containerObj = new DateTime();
				break;
			case 'date':
				$containerObj = new Date();
				break;
			case 'time':
				$containerObj = new Time();
				break;
			case 'file':
				$containerObj = new File();
			case 'bool':
			case 'boolean':
				$containerObj = new Boolean();
				break;
			case 'string':
			case 'text':
				$containerObj = new Text();
				break;
			default:
				throw new \Exception("Unknown field type $type");
		}

		return $containerObj;
	}
}