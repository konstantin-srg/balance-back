<?php

namespace Pipe\DModel;

use Pipe\StdLib\Singleton;

class Links
{
	use Singleton;

	const TYPE_OTO = 'oto';
	const TYPE_OTM = 'otm';
	const TYPE_MTM = 'mtm';
	const TYPE_MTO = 'mto';

	protected array $links = [];


	protected array $children = [];
	protected array $parents = [];
	public function addLink($config): void
	{

		foreach ($config as $class => $links) {
			foreach ($links as $field => $link) {
				$this->links[$class]['parents'] = [
					'field'    => $field,
					'class'    => $link['parent'],
					//'type'     => $link['type'],
					//'actions'  => $link['actions'],
				];


				$this->links[$link['parent']]['children'] = [
					'field'    => $field,
					'class'    => $class,
					'type'     => $link['type'] == self::TYPE_MTO ? self::TYPE_OTM : self::TYPE_OTO,
					'actions'  => $link['actions'],
				];
			}
		}
	}
}