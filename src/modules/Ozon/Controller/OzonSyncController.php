<?php
namespace Ozon\Controller;

use Ozon\Model\OzonApi;
use Ozon\Model\OzonSync;
use Ozon\Model\Test;
use Ozon\Service\OzonSyncService;
use Pipe\Debug\Dumper\Dumper;
use Pipe\Debug\Timer;
use Pipe\DModel\Containers\DateTime;
use Pipe\Mvc\Controller\AController;

/**
 * @method OzonSyncService service()
 */
class OzonSyncController extends AController {

	public function test()
	{
//		$this->syncService()->test();
//		$this->syncService()->updateClusters();
		//$this->syncService()->updateProducts();
		//$this->syncService()->updateOzonProducts();
		//$this->syncService()->updateOzonSku();
		//$this->syncService()->updateOzonClusters();
		//$this->syncService()->updateOzonOrders(['page' => 1, 'date' => new \DateTime('2025-11-01')]);
		$this->syncService()->updateExpenses(['page' => 1, 'date' => new \DateTime('2025-11-01')]);
		//$this->syncService()->updateOzonStatistic();
		//$this->syncService()->updateExpenses();

		//$this->syncService()->test();
		//$this->syncService()->updateStatistic();
		dd();
	}

	protected $maps = [
		'clusters' => [
			'name'    => 'Обновление информации о кластерах',
			'func'    => 'updateOzonClusters',
			'expire'  => 20*60,
		],
		'products' => [
			'name' => 'Обновление информации о товарах',
			'func'    => 'updateProducts',
			'expire'  => 20*60,
		],
		'ozonProducts' => [
			'name' => 'Соединение товаров с Ozon',
			'func'    => 'updateOzonProducts',
			'expire'  => 20*60,
		],
		'updateOzonSku' => [
			'name' => 'Обновление SKU товаров',
			'func'    => 'updateOzonSku',
			'expire'  => 20*60,
		],
		'stock'    => [
			'name' => 'Обновление остатков на складах',
			'func'    => 'updateOzonStock',
			'expire'  => 20*60,
		],
		'statistic'    => [
			'name' => 'Обновление статистики Ozon',
			'func'    => 'updateOzonStatistic',
			'expire'  => 20*60,
		],
		'orders'    => [
			'name' => 'Статистика заказов с Ozon',
			'func'    => 'updateOzonOrders',
			'expire'  => 20*60,
		],
		'expenses'    => [
			'name' => 'Статистика расходов Ozon',
			'func'    => 'updateExpenses',
			'expire'  => 1,
		],
	];

	public function map()
	{
		foreach ($this->maps as $type => $map) {
			$maps[$type] = [
				'name' => $map['name'],
				'map'  => $type,
				'url'  => '/ozon/sync',
			];
		}

		$map = [];

		switch ($this->params('map')) {
			case 'stock':
				$map[] = $maps['clusters'];
				$map[] = $maps['products'];
				$map[] = $maps['ozonProducts'];
				$map[] = $maps['updateOzonSku'];
				$map[] = $maps['stock'];
				break;
			default:
				return $this->response(['errors' => ["Unknown sync map type '{$this->params('map')}'"], 'status' =>  'success']);
		}

		$ozonAcc = OzonApi::findById($this->params('api'));

		if(!$this->params('api') || !($ozonAcc = OzonApi::findById($this->params('api')))) {
			return $this->response(['status' =>  'fail', 'info' => ['Неизвестный аккаунт Ozon']]);
		}

		//sleep(1);
		return $this->response(['result' => $map, 'status' =>  'success', 'info' => ['Обновление аккаута ozon: ' . $ozonAcc->name]]);
	}

	public function sync()
	{
		$mapName = $this->params('map');
		$page = $this->params(key: 'page',default: 1);

//		$result = $this->syncService()->test();
//		return $this->response(['status' =>  'fail']);

		try {
			$date = new \DateTime($this->params('date'));
			if(!$date) throw new \Exception();
		} catch (\Exception $e) {
			return $this->response(['errors' => ['Parsing date error'], 'status' => 'fail']);
		}

		try {
			if($map = $this->maps[$mapName]) {
				$sync = OzonSync::findOrCreate([], ['key' => 'ozon_sync_' . $mapName]);

				/*if(!$sync->isExpired($map['expire'])) {
					return $this->response([
						'warning'   => sprintf('С прошлого успешного обновления не прошло %s мин', $map['expire'] / 60),
						'status'    => 'fresh',
					]);
				}*/

				$func = $map['func'];
				$result = $this->syncService()->$func(['ozonApiId' => $this->params('api'), 'page' => $page, 'date' => $date]);

				if($result['resolve']) {
					$sync->set('sync', 'now')->save();
				}

				return $this->response($result);
			}
		} catch (\Exception $e) {
			return $this->response(['errors' => [$e->getMessage()], 'status' => 'fail']);
		}

		return $this->response(['errors' => ["Update map '{$mapName}' not found"], 'status' => 'fail']);
	}

	protected function response($resp)
	{
		return array_merge([
			'processed' => 0,
			'status'    => 'success',
			'time'      => Dumper::getInstance()->timer()->get(),
			'warnings'  => [],
			'errors'    => [],
			'resolve'   => false,
			'result'    => [],
			'info'      => [],
		], $resp);
	}

	protected function syncService(): OzonSyncService
	{
		return $this->sm()->get(OzonSyncService::class);
	}
}