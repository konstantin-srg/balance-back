<?php
namespace Ozon\Controller;

use Catalog\Model\Products;
use Ozon\Model\OzonClusters;
use Ozon\Model\OzonProducts;
use Ozon\Model\OzonStock;
use Ozon\Service\OzonSyncService;
use Pipe\Debug\Timer;
use Pipe\DModel\DBAdapter\Sql\Skip;
use Pipe\DModel\DBAdapter\Sql\Where;
use Pipe\DModeler\DModel\Containers\DateTime;
use Pipe\Mvc\Controller\AController;

/**
 * @method OzonSyncService service()
 */
class OzonController extends AController {
	public function getStock()
	{
		$data = [];
		foreach (OzonStock::list() as $item) {
			$key = $item->oz_prod_id . '|' . $item->cluster_id;
			$data[$key] = [
				'id'        => $key,
				'quantity'  => $item->quantity,
				'avg_sales' => round($item->avg_sales, 1),
			];
		}
		return $data;
	}

	public function getClusters()
	{
		$where = [];
		switch ($this->params()['region']) {
			case 'russia': $where['russia'] = 1; break;
			case 'other':  $where['russia'] = 0; break;
		}
		$items = OzonClusters::list(['id', 'name'], $where);
		$items->select()->order(['sort' => 'desc']);

		$data = [];
		foreach ($items as $item) {
			$data[] = [
				'id'    => $item->id,
				'name'  => $item->name,
			];
		}
		return $data;
	}

	public function getProducts()
	{
		$params = $this->params();
		$list = Products::list(['id', 'name'], [
			'brand_id' => $params['brand'] ?: Where::SKIP,
			'tag_id'   => $params['tag']   ?: Where::SKIP,
			'active'   => 1,
		]);
		$list->select()->order(['sort' => 'desc']);
		$list->join('ozon');

		$data = [];
		foreach ($list as $item) {
			$data[] = [
				'id'    => $item->ozon()->id(),
				'name'  => $item->name,
			];
		}
		return $data;
	}

	public function getStockTable()
	{
		$clusters = $this->getClusters();
		$products = $this->getProducts();
		$stock    = $this->getStock();

		$result = [];

		foreach ($products as $product) {
			$productRow = [
				'product' => $product,
				'stock' => []
			];

			foreach ($clusters as $cluster) {
				$stockItem = $stock[$product['id'] . '|' . $cluster['id']];
				if(!isset($stockItem)) {
					$stockItem =  [
						'quantity'  => 0,
						'avg_sales' => 0,
					];
				}

				if($stockItem && $stockItem['avg_sales'] > 0) {
					$status = min(4, ceil($stockItem['quantity'] / ($stockItem['avg_sales'] * 7)));
					$stockItem['status'] = $status;
				}

				$productRow['stock'][] = $stockItem;
			}

			$result[] = $productRow;
		}

		$clustersResp = [];
		foreach ($clusters as $cluster) {
			$clustersResp[] = $cluster;
		}

		return [
			'clusters' => $clustersResp,
			'stock'    => $result,
		];
	}

	protected function sync(): OzonSyncService
	{
		return $this->sm()->get(OzonSyncService::class);
	}
}