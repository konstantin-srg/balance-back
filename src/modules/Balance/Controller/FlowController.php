<?php
namespace Balance\Controller;

use Balance\Model\Balance;
use Balance\Model\Flow;
use Catalog\Model\Products;
use Ozon\Model\OzonProducts;
use Ozon\Model\OzonStock;
use Ozon\Service\OzonSyncService;
use Pipe\Mvc\Controller\AController;

class FlowController extends AController {
  public function flowProducts()
  {
		$sum = 'SUM(statistic.sales * (statistic.profit - t.cost))';


	  $products = Products::list(['name', 'image', 'cost',
		  'sum' => $sum]);
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
	  //$products->select()->order(['COALESCE(NULL, stock, 0)' => $direction]);
	  $products->select()->order(['COALESCE(NULL, ' . $sum . ', 0)' => $direction]);

	  $items = [];
	  foreach ($products as $product) {
		  $items[] = [
			  'product' => [
				  'id'    => $product->id,
				  'image' => $product->image,
				  'name'  => $product->name,
				  'stock' => $product->ozon()->statistic()->stock,
			  ],
			  'profit' => $product->ozon()->statistic()->profit,
			  'sales' => $product->ozon()->statistic()->sales,
			  'cost'  => $product->cost,
			  'sum'   => (int) $product->sum,
		  ];
	  }

	  return $items;
  }

	public function flowAdd()
	{
		$params = $this->params();

		$balance = new Flow();
		$balance->set(['name' => $params['name'], 'sum' => str_replace(' ', '', $params['sum']), 'date' => $params['date']]);
		$balance->save();

		return [
			'status' => 1
		];
	}
	public function flowDel()
	{
		$balance = new Flow();
		$balance->id($this->params('id'));
		$balance->remove();

		return [];
	}

	public function flowList()
	{
		$dt = new \DateTime($this->params()['date']);
		$balance = Flow::list(['name', 'sum', 'date']);
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