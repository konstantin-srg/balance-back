<?php
namespace Balance\Controller;

use Balance\Model\Balance;
use Catalog\Model\Products;
use Ozon\Model\OzonProducts;
use Ozon\Model\OzonStock;
use Ozon\Service\OzonSyncService;
use Pipe\Mvc\Controller\AController;

class BalanceController extends AController {
  public function balance()
  {

	  $products = Products::list(['name', 'image', 'cost',
		  'sum' => 'SUM(t.cost * statistic.stock)']);
	  $products
		  ->join('ozon', ['ozon_id'])
		  ->down()
		    ->join('statistic', ['profit', 'sales', 'stock']);

	  $products->select()
		  //->join(['stock' => OzonStock::table()], 'ozon.id = stock.oz_prod_id', ['ozon-stock' => 'SUM(stock.quantity)'])
		  ->group('auto');

	  $params = $this->params();
	  if($params['product']) {
		  $products->select()->where("LOWER(t.name) LIKE LOWER(?)", '%' . $params['product'] . '%');
	  }

	  $direction = in_array($params['sort']['direction'], ['asc', 'desc']) ? $params['sort']['direction'] : 'asc';
	  $products->select()->order(['COALESCE(NULL, stock, 0)' => $direction]);

		//return $_COOKIE;

	  /*if(isset($params['sort']) && is_array($params['sort'])) {
		  $direction = in_array($params['sort']['direction'], ['asc', 'desc']) ? $params['sort']['direction'] : 'asc';
		  $sort = $params['sort'];

		  if($params['sort']['field'] == 'stock') {
			  $products->select()->order(['stock' => $direction]);
			  //dd($products->select());
		  }

		  if($params['sort']['field'] == 'cost') {
			  $products->select()->order(['cost' => $direction]);
		  }

		  if($params['sort']['field'] == 'sum') {
			  $products->select()->order(['sum' => $direction]);
		  }
	  }*/


	  $items = [];
	  foreach ($products as $product) {
		  $items[] = [
			  'product' => [
				  'id'    => $product->id,
				  'image' => $product->image,
				  'name'  => $product->name,
				  'stock' => $product->ozon()->statistic()->stock,
			  ],
			  'stock' => $product->ozon()->statistic()->stock,
			  'cost'  => $product->cost,
			  'sum'   => (int) $product->sum,
		  ];
	  }

	  return $items;
  }

	public function flowAdd()
	{
		$params = $this->params();

		$balance = new Balance();
		$balance->set(['name' => $params['name'], 'sum' => str_replace(' ', '', $params['sum']), 'date' => $params['date']]);
		$balance->save();

		return [
			'status' => 1
		];
	}
	public function flowDel()
	{
		$balance = new Balance();
		$balance->id($this->params('id'));
		$balance->remove();

		return [];
	}

	public function flowList()
	{
		$dt = new \DateTime($this->params()['date']);
		$balance = Balance::list(['name', 'sum', 'date']);
		$balance->select()->where([
			'date >= ?' => $dt->format('Y-m-01'),
			'date <= ?' => $dt->modify('last day of this month')->format('Y-m-d'),
		]);

		$result = [];
		foreach ($balance as $row) {
			$result[] = [
				'id'    => $row->id,
				'name'  => $row->name,
				'sum'   => $row->sum,
				'date'  => $row->date->format('Y-m-d'),
			];
		}

		return $result;
	}

	protected function getOzonService(): OzonSyncService
	{
		return $this->sm()->get('Ozon\Service\OzonSyncService');
	}
}