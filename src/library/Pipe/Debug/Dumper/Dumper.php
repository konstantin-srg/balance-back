<?php

namespace Pipe\Debug\Dumper {

	use Pipe\Debug\Timer;
	use Pipe\Exception\Exception;
	use Pipe\StdLib\TSingleton;

	class Dumper
	{
		use TSingleton;

		protected $renderer;
		protected $requests;

		protected $steck = [];

		protected $timer;

		protected function __construct()
		{
			$this->timer = new Timer(false);
		}

		public function start()
		{
			$this->timer->start();
		}

		public function timer()
		{
			return $this->timer;
		}

		public function setRenderer(Renderer $renderer)
		{
			$this->renderer = $renderer;
		}

		public function renderer(): Renderer
		{
			if (!$this->renderer) $this->renderer = new Renderer();

			return $this->renderer;
		}

		public function die($var = 'END', $key = '')
		{
			$this->dump($var, $key);

			$dumpId = 'dump-' . Rand(0, 9999);

			$htmlDumps =
				'<div class="list">';

			foreach ($this->steck as $tmp) {
				$htmlDumps .=
					'<div class="item">' .
						$this->renderer()->caller($tmp['caller'], $tmp['time']) .
						$this->dumpVar($tmp['key'], $tmp['var']) .
					'</div>';
			}

			$htmlDumps .=
				'</div>';

			return
				$this->renderer()->layout(
					'window ' . $dumpId,
					$htmlDumps .
					'<script>' .
					file_get_contents(__DIR__ . '/skins/script.js') . "\n" .
					'initDump("' . $dumpId . '")' .
					'</script>');
		}

		public function dump($var, $key = '')
		{
			$this->steck[] = [
				'key' => $key,
				'var' => $var,
				'caller' => $this->getCallerInfo(),
				'time' => $this->timer()->get(),
			];
		}

		protected function getCallerInfo()
		{
			foreach (debug_backtrace() as $step) {
				if (str_ends_with($step['file'], 'Dumper.php')) continue;

				$caller = $step;
				break;
			}

			return [
				'file' => substr($caller['file'], strlen(BASE_DIR)),
				'line' => $caller['line'],
			];
		}

		protected function dumpVar($key, $val, &$html = '')
		{
			if (!isset($val)) {
				return $this->tNull($key, null);
			}

			return match (gettype($val)) {
				'array' => $this->tArray($key, $val),
				'string' => $this->tString($key, $val),
				'double' => $this->tDouble($key, $val),
				'integer' => $this->tInteger($key, $val),
				'object' => $this->tObject($key, $val),
				'boolean' => $this->tBool($key, $val),
				'NULL' => $this->tNull($key, $val),
				default => throw new Exception('Unknown var "' . gettype($val) . '" type'),
			};
		}

		protected function tBool($key, $val)
		{
			return $this->renderer()->row($val ? 'tru' : 'fal', $key, $val ? 'true' : 'false');
		}

		protected function tNull($key, $val)
		{
			return $this->renderer()->row('Int', $key, 'null');
		}

		protected function tString($key, $val)
		{
			return $this->renderer()->row('Str', $key, $val !== '' ? '' . nl2br(htmlspecialchars($val)) . '' : '""');
		}

		protected function tDouble($key, $val)
		{
			return $this->renderer()->row('Dbl', $key, $val);
		}

		protected function tInteger($key, $val)
		{
			return $this->renderer()->row('Int', $key, $val);
		}

		protected function tArray($key, $var)
		{
			$html = '';
			foreach ($var as $subKey => $val) {
				$html .= $this->dumpVar($subKey, $val);
			}
			return $this->renderer()->folder('Arr', $key ?: 'arr', $html);
			//return $this->renderer()->folder('Arr', $key . '-' . $subKey , $html);
		}

		protected function tObject($key, $val)
		{
			if(method_exists(get_class($val), '__dump')) {
				return $this->dumpVar($key, $val->__dump());
			}

			if(method_exists($val, 'toArray')) {
				return $this->tArray($key, $val->toArray());
			}

			$reflection = new \ReflectionObject($val);
			$properties = array();
			foreach ($reflection->getProperties() as $property) {
				$property->setAccessible(true);
				$properties[$property->getName()] = $property->getValue($val);
			}
			//return array_merge((array) $reflection->getConstants(), $properties);

			$html = '';
			foreach ($properties as $subKey => $subVal) {
				$html .= isset($subVal) ? $this->dumpVar($subKey, $subVal) : $this->dumpVar($subKey, null);
			}
			return $this->renderer()->folder('Obj', $key ?: get_class($val), $html, ['{', '}']);
		}
	}
}

namespace //
{
	use Pipe\Debug\Dumper\Dumper;

	function d($data = '', $key = '')
	{
		Dumper::getInstance()->dump($data, $key);
	}

	function dd($data = 'END', $key = '')
	{
		die(Dumper::getInstance()->die($data, $key));
	}
}


