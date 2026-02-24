<?php
namespace Ozon\Service;

use Balance\Model\Flow;
use Catalog\Model\Brands;
use Catalog\Model\Products;
use JetBrains\PhpStorm\ArrayShape;
use Ozon\Model\OzonClusters;
use Ozon\Model\OzonProducts;
use Ozon\Model\OzonProductsSku;
use Ozon\Model\OzonStatistic;
use Ozon\Model\OzonStock;
use Pipe\Debug\Timer;
use Pipe\Mvc\Service\AService;
use Pipe\Ozon\Ozon;

class OzonSyncService extends AService {
	public function updateOzonOrders($options)
	{
		$skuMdl = OzonProductsSku::list(['sku', 'oz_prod_id']);
		$skuMdl
			->join('ozon', ['ozon_id'])
			->down()
				->join('product', ['name', 'cost']);

		$skusCost = [];

		$warnings = [];

		foreach ($skuMdl as $item) {
			try {
				$skusCost[$item->sku] = [
					'name'  => $item->ozon()->product()->name,
					'cost'  => $item->ozon()->product()->cost,
				];
			} catch (\Exception $e) {
				$warnings[] = 'не найден товар с SKU: ' . $item->sku;
			}
		}

		$from = new \DateTime(); $from->modify('-30 days');
		$page = 30;

		$result = [];
		$operations = 0;

		$types = [
			'OperationAgentDeliveredToCustomer' => 'Доставка заказов',
			'OperationAgentDeliveredToCustomerCanceled' => 'доставка: исправленное начисление',
			'OperationAgentStornoDeliveredToCustomer' => 'доставка: отмена начисления',
		];

		do {
			$resp = $this->ozon()->send('v3/finance/transaction/list', [
				'filter' => [
					'date' => [
						'from' => $from->modify('first day of this month')->format('Y-m-d\\TH:i:sP'),
						'to'   => $from->modify('last day of this month')->format('Y-m-d\\TH:i:sP'),
					],
					'operation_type' => array_keys($types),
				],
				'page' => $page,
				'page_size' => 1000
			]);

			foreach ($resp['result']['operations'] as $operation) {
				$operations++;
				$key = $operation['operation_type'];
				if(!isset($result[$key])) {
					$result[$key] = 0;
				}
				$result[$key] += $operation['amount'];

				foreach ($operation['items'] as $item) {
					$sku = $item['sku'];
					if(!isset($skusCost[$sku])) { $warnings[] = 'Не найден SKU: ' . $sku; continue; }
					if(!$skusCost[$sku]['cost']) { $warnings[] = 'Не найдена цена товара: ' . $skusCost[$sku]['name']; continue; }
					$result['ProductsCost'] += -$skusCost[$sku]['cost'];
				}
			}

			$page++;
		} while(!empty($resp['result']) && $page <= 35);

		$types['ProductsCost'] = 'Себестоимость товаров';

		$data = [];
		foreach ($result as $type => $sum) {
			if(!isset($data[$type])) {
				$data[$type] = [
					'create' => [
						'key'  => 'Ozon' . $type,
						'name' => $types[$type],
						'date' => $from->modify('first day of this month')->format('Y-m-d'),
					],
					'update' => [
						'sum' => 0,
					],
				];
			}

			$data[$type]['update']['sum'] += $sum;
		}

		$flow = Flow::list(['key']);
		$flow->select()->where(['date' => $from->modify('first day of this month')->format('Y-m-d')]);

		$flow->fullUpdate(
			compare: function ($model, $row) {
				return $model->key == $row['create']['key'];
			},
			data: $data,
			delete: false
		)->save();

		d($operations);
		dd($warnings);

		return $operations;

		/*$skus = OzonProductsSku::list(['sku', 'oz_prod_id']);

		$data = [];
		$from = new \DateTime();

		$sales = 0;
		$page = 1;
		do {
			$resp = $this->ozon()->send('v3/finance/transaction/list', [
				'filter' => [
					'date' => [
						'from' => $from->modify('first day of this month')->format('Y-m-d\\TH:i:sP'),
						'to'   => $from->modify('last day of this month')->format('Y-m-d\\TH:i:sP'),
					],
					'operation_type' => ['OperationAgentDeliveredToCustomer', 'OperationAgentDeliveredToCustomerCanceled',
						'OperationAgentStornoDeliveredToCustomer',],
				],
				'page' => $page,
				'page_size' => 5,
			]);

			dd($resp);

			foreach ($resp['result']['operations'] as $transaction) {
				$sku = $transaction['items'][0]['sku'];
				if(!isset($data[$sku])) {
					if(!($ozSku = $skus->pipe()->find(['sku' => $sku])->one())) {
						//throw new \Exception('Can`t find ozon product with sku : ' . $sku);
						continue;
					}

					$data[$sku] = [
						'create' => [
						  'oz_prod_id' => $ozSku->oz_prod_id,
						],
						'update' => [
							'profit'     => 0,
							'commission' => 0,
						]
					];
				}

				$sales++;

				$break = false;
				if(($n = count($transaction['items'])) > 1) {
					foreach ($transaction['items'] as $item) {
						if($sku !== $item['sku']) $break = true;
					}
				}
				if ($break) continue;


				//$data[$sku]['update']['sales']      += $n;
				$data[$sku]['update']['profit']     += $transaction['amount'] / $n;
				$data[$sku]['update']['commission'] += ($transaction['accruals_for_sale'] - $transaction['amount']) / $n;
			}

			$page++;
		} while(!empty($resp['result']['operations']) && $page <= 40);

		foreach ($data as &$product) {
			$product['update']['profit']     = round($product['update']['profit'] / $product['update']['sales']);
			$product['update']['commission'] = round($product['update']['commission'] / $product['update']['sales']);
		}

		$statistic = OzonStatistic::list(['oz_prod_id']);
		$statistic->fullUpdate(
			compare: function ($model, $row) {
				return $model->oz_prod_id == $row['create']['oz_prod_id'];
			},
			data: $data,
			delete: false,
		)->save();


		$flow = Flow::list(['key']);
		$flow->select()->where(['date' => $from->modify('first day of this month')->format('Y-m-d')]);
		$flow->fullUpdate(
			compare: function ($model, $row) {
				return $model->key == $row['create']['key'];
			},
			data: [
				'create' => [
					'key'  => 'OzonOperationAgentDeliveredToCustomer',
					'name' => 'Продажа товара',
					'date' => $from->modify('first day of this month')->format('Y-m-d'),
				],
				'update' => [
					'sum' => $sum,
				],
			],
			delete: false
		)->save();

		return $sales;*/
	}


	public function updateProducts()
	{
		$data = [];
		$loadProducts = function ($type) use (&$data) {
			$resp = $this->ozon()->send('v3/product/list', [
				'filter' => [
					'visibility' => $type
				],
				'limit'  => 1000
			]);

			foreach ($resp['result']['items'] as $item) {
				$data[$item['product_id']] = [
					'create' => [
						'barcode' => $item['offer_id'],
					],
					'update' => []
				];
			}
		};

		$loadProducts('IN_SALE');
		$loadProducts('TO_SUPPLY');

		$brands = Brands::list(['name']);
		$findBrand = function ($name) use ($brands) {
			foreach ($brands as $brand) {
				if(mb_stripos($name, $brand->name) !== false) return $brand->id;
			}
			return 0;
		};

		$ozonIds = array_keys($data);
		array_walk($ozonIds, function (&$value) { $value = (string) $value; });
		$resp = $this->ozon()->send('v3/product/info/list', [
			'product_id' => $ozonIds
		]);

		foreach ($resp['items'] as $ozonProduct) {
			$data[$ozonProduct['id']]['create'] += [
				'name'      => $ozonProduct['name'],
				'brand_id'  => $findBrand($ozonProduct['name']),
			];

			$data[$ozonProduct['id']]['update'] += [
				'image' => $ozonProduct['primary_image'][0],
			];
		}

		$products = Products::list(['barcode']);
		$products->fullUpdate(
			compare: function ($model, $row) {
				return $model->barcode == $row['create']['barcode'];
			},
			data: $data
		)->save();

		return [
			'processed' => count($data),
			'resolve'   => true,
		];
	}

	public function updateOzonProducts()
	{
		$products = Products::list(['barcode']);

		$data = [];
		$loadProducts = function ($type) use (&$data, &$products) {
			$resp = $this->ozon()->send('v3/product/list', [
				'filter' => [
					'visibility' => $type
				],
				'limit'  => 1000
			]);

			foreach ($resp['result']['items'] as $item) {

				if(!($product = $products->pipe()->find(['barcode' => $item['offer_id']])->one())) {
					throw new \Exception('Can`t find product with barcode : ' . $item['offer_id']);
				}

				$data[$item['product_id']] = [
					'create' =>[
						'ozon_id'      => $item['product_id'],
						'offer_id'     => $item['offer_id'],
						'product_id'   => $product->id(),
						'active'       => false,
					],
					'update' =>[
						'archived'    => !$item['archived'],
					],
				];
			}
		};

		$loadProducts('IN_SALE');
		$loadProducts('TO_SUPPLY');

		$pProducts = OzonProducts::list(['ozon_id']);
		$pProducts->fullUpdate(
			compare: function ($model, $row) {
				return $model->ozon_id == $row['create']['ozon_id'];
			},
			data: $data
		)->save();

		return count($data);
	}

	public function updateOzonSku()
	{
		$ozProducts = OzonProducts::list(['ozon_id']);
		$ozonIds = [];
		foreach ($ozProducts as $ozProduct) {
			$ozonIds[] = $ozProduct->ozon_id;
		}

		$resp = $this->ozon()->send('v3/product/info/list', [
			'product_id' => $ozonIds
		]);

		$data = [];

		foreach ($resp['items'] as $item) {
			foreach ($item['stocks']['stocks'] as $ozonStock) {
				$data[] = [
					'create' => [
						'oz_prod_id' => $ozProducts->pipe()->find(['ozon_id' => $item['id']])->one()->id(),
						'sku'        => $ozonStock['sku'],
						'active'     => $ozonStock['present'] + $ozonStock['reserved'] > 0,
					],
				];
			}
		}

		$ozProdSku = OzonProductsSku::list(['oz_prod_id', 'sku']);
		$ozProdSku->fullUpdate(
			compare: function ($model, $row) {
				return $model->sku == $row['create']['sku'];
			},
			data: $data,
			update: false,
			delete: false
		)->save();
	}

	public function updateExpenses(#[ArrayShape(['page' => 'int', 'date' => \DateTime::class])] $options = [])
	{
		$cacheKey = 'sync-ozon-expenses';
		$result = $options['page'] == 1 ? [] : ($this->cache()->get($cacheKey) ?: []);
		$operations = 0;

		$types = [
//			'OperationAgentDeliveredToCustomer' => 'доставка покупателю',
//			'OperationAgentDeliveredToCustomerCanceled' => 'доставка покупателю: исправленное начисление',
//			'OperationAgentStornoDeliveredToCustomer' => 'доставка покупателю: отмена начисления',

			'ClientReturnAgentOperation' => 'Возврат от покупателя',
			'MarketplaceMarketingActionCostOperation' => 'Услуги продвижения товаров',
			'MarketplaceSaleReviewsOperation' => 'Приобретение отзывов',
			'MarketplaceSellerCompensationOperation' => 'Прочие компенсации',
			'OperationClaim' => 'Начисление по претензии',
			'OperationCorrectionSeller' => 'Инвентаризация взаиморасчетов',
			'OperationDefectiveWriteOff' => 'Компенсация за повреждённый на складе товар',
			'OperationItemReturn' => 'Доставка и обработка возврата',
			'OperationLackWriteOff' => 'Компенсация за утерянный товар',
			'OperationMarketplaceCrossDockServiceWriteOff' => 'Кросс-докинг',
			'OperationMarketplaceServiceStorage' => 'Размещение товаров на складе',
			'OperationSetOff' => 'Взаимозачёт по другими договорам',
			'MarketplaceSellerReexposureDeliveryReturnOperation' => 'Перечисление за доставку от покупателя',
			'OperationReturnGoodsFBSofRMS' => 'Доставка и обработка возврата',
			'ReturnAgentOperationRFBS' => 'возврат перечисления за доставку покупателю',
			'ItemAgentServiceStarsMembership' => 'Вознаграждение за услугу «Звёздные товары»',
			'MarketplaceSellerShippingCompensationReturnOperation' => 'компенсация перечисления за доставку',
			'OperationMarketplaceServicePremiumCashback' => 'услуга продвижения Premium',
			'MarketplaceServicePremiumPromotion' => 'услуга продвижения Premium, фиксированная комиссия',
			'MarketplaceRedistributionOfAcquiringOperation' => 'оплата эквайринга',
			'MarketplaceReturnStorageServiceAtThePickupPointFbsItem' => 'краткосрочное размещение возврата FBS',
			'MarketplaceReturnStorageServiceInTheWarehouseFbsItem' => 'долгосрочное размещение возврата FBS',
			'MarketplaceServiceItemDeliveryKGT' => 'доставка КГТ',
			'MarketplaceServiceItemDirectFlowLogistic' => 'логистика',
			'MarketplaceServiceItemReturnFlowLogistic' => 'обратная логистика',
			'MarketplaceServicePremiumCashbackIndividualPoints' => 'услуга продвижения «Бонусы продавца»',
			'OperationMarketplaceWithHoldingForUndeliverableGoods' => 'удержание за недовложение товара',
			'MarketplaceServiceItemDirectFlowLogisticVDC' => 'логистика вРЦ',
			'MarketplaceServiceItemDropoffPPZ' => 'услуга drop-off в пункте приёма заказов',
			'MarketplaceServicePremiumCashback' => 'услуга продвижения Premium',
			'MarketplaceServiceItemRedistributionReturnsPVZ' => 'перевыставление возвратов на пункте выдачи',
			'OperationElectronicServiceStencil' => 'услуга «Трафареты»',
			'OperationElectronicServicesPromotionInSearch' => 'услуга «Продвижение в поиске»',
			'OperationMarketplaceServiceItemElectronicServicesBrandShelf' => 'услуга «Брендовая полка»',
			'OperationSubscriptionPremium' => 'подписка Premium.'
		];


		$apiCalls = 2;

		$dateFirst = ($options['date'])->modify('first day of this month');
		$dateLast  = (clone $options['date'])->modify('last day of this month');

		$startPage = ($options['page'] - 1) * $apiCalls + 1;

		// while($continue && $page <= ($options['page'] + 1));

		$totalTime   = new Timer(true);
		$requestTime = new Timer(false);

		for($i = 0, $continue = true; $i < $apiCalls && $continue; $i++) {
			$requestTime->start();
			$resp = $this->ozon()->send('v3/finance/transaction/list', [
				'filter' => [
					'date' => [
						'from' => $dateFirst->format('Y-m-d\\TH:i:sP'),
						'to'   => $dateLast->format('Y-m-d\\TH:i:sP'),
					],
					'operation_type' => array_keys($types),
				],
				'page' => $startPage + $i,
				'page_size' => 500
			]);
			$requestTime->stop();

			foreach ($resp['result']['operations'] as $operation) {
				$operations++;

				$key = $operation['operation_type'];
				if(!isset($result[$key])) {
					$result[$key] = 0;
				}
				$result[$key] += $operation['amount'];
			}

			$continue = !empty($resp['result']['operations']);
		}

		if($continue) {
			$this->cache()->set($cacheKey, $result);
			return $this->response([
				'info'      => ["Ожидание Ozon API {$requestTime->get()} сек (" . (round($requestTime->get() / $totalTime->get(), 2) * 100) . "%)"],
				'processed' => $operations,
				'continue'  => true,
			]);
		}

		$this->cache()->del($cacheKey);

		$data = [];
		foreach ($result as $type => $sum) {
			if(!isset($data[$type])) {
				$data[$type] = [
					'create' => [
						'key'  => 'Ozon' . $type,
						'name' => $types[$type],
						'date' => $dateFirst->format('Y-m-d'),
					],
					'update' => [
						'sum' => 0,
					],
				];
			}

			$data[$type]['update']['sum'] += $sum;
		}

		$flow = Flow::list(['key']);
		$flow->select()->where(['date' => $dateFirst->format('Y-m-d')]);

		$flow->fullUpdate(
			compare: function ($model, $row) {
				return $model->key == $row['create']['key'];
			},
			data: $data,
			delete: false
		)->save();

		return $this->response([
			'processed' => $operations,
			'info'      => ["Ожидание Ozon API {$requestTime->get()} сек (" . (round($requestTime->get() / $totalTime->get(), 2) * 100) . "%)"],
		]);
	}

	public function updateOzonStatistic()
	{
		$ozClusters = OzonClusters::list(['ozon_id']);
		$ozProducts = OzonProducts::list(['offer_id']);

		$skus = OzonProductsSku::list(['sku'], ['active' => 1]);
		//dd($skus);
		$skuList = [];
		foreach ($skus as $sku) {
			$skuList[] = $sku->sku;
		}

//		if(count($skuList) > 100) {
//			throw new \Exception("Too much skus to update (" . count($skuList) . ")");
//		}

		$operations = 0;
		$page = 0;
		$delivering = [];
		/*do {
			$resp = $this->ozon()->send('v2/posting/fbo/list', [
				'filter' => [
					'status' => 'delivering',
				],
				'offset' => $page * 1000,
				'limit' => 1000
			]);

			foreach ($resp['result'] as $orders) {
				foreach ($orders['products'] as $item) {
					if(!isset($delivering[$item['offer_id']])) {
						$ozProduct = $ozProducts->pipe()->find(['offer_id' => $item['offer_id']])->one();
						if(!$ozProduct) {
							throw new \Exception('Can`t find product:  ' . $item['name']);
						}

						$delivering[$item['offer_id']] = ['create' => [
							'oz_prod_id'  => $ozProduct->id(),
						], 'update' => [
							'stock'  => 0,
							'sales'  => 0,
						]];
					}

					$delivering[$item['offer_id']]['update']['stock'] += $item['quantity'];
					$operations++;
				}
			}
			$page++;
		} while(!empty($resp['result']) && $page <= 5);/*

		//dd($delivering);
		/*$resp = $this->ozon()->send('v1/returns/list', [
			'filters' => [
				'visual_status_name' => ['MovingToOzon']
			],
			'limit' => 500
		]);
		dd($resp);*/

		//https://api-seller.ozon.ru/v2/posting/fbo/list

		$result = [];
		$limit = 90;

		for($i = 0; $i < ceil(count($skuList) / $limit); $i++) {
			$resp = $this->ozon()->send('v1/analytics/stocks', [
				'skus' => array_slice($skuList, ($i*$limit), (($i+1)*$limit))
			]);

			$result = array_merge($result, $resp['items']);

		}

		$data = [];

		foreach ($result as $ozonStock) {
			if(!$ozonStock['available_stock_count']) {
				continue;
			}

			$cpId = $ozonStock['cluster_id'] . '|' . $ozonStock['offer_id'];

			$ozProduct = $ozProducts->pipe()->find(['offer_id' => $ozonStock['offer_id']])->one();
			if(!$ozProduct) { dd($ozonStock); }

			$cluster = $ozClusters->pipe()->find(['ozon_id' => $ozonStock['cluster_id']])->one();

			if(!$cluster) {
				//throw new \Exception('Unknown ozon cluster "' . $ozonStock['cluster_name'] . '"');
				//d($ozonStock);
				continue;
			}

			if(!isset($data[$cpId])) {
				$data[$cpId] = ['create' => [
					'cluster_id'  => $cluster->id(),
					'oz_prod_id'  => $ozProduct->id(),
					'avg_sales'   => $ozonStock['ads_cluster'] ?? 0,
					'quantity'    => 0,
				]];
			}

			$quantity =
				$ozonStock['available_stock_count'] + // Количество товаров, которые доступны к продаже
				$ozonStock['other_stock_count'] + // Количество единиц товара, проходящих проверку.
				$ozonStock['transit_stock_count']; //Количество единиц товара в поставках в пути.

			$data[$cpId]['create']['quantity'] += $quantity; //Количество единиц товара в поставках в пути.

			if(!isset($delivering[$ozonStock['offer_id']])) {
				$ozProduct = $ozProducts->pipe()->find(['offer_id' => $ozonStock['offer_id']])->one();
				if(!$ozProduct) { dd($ozonStock); }

				$delivering[$ozonStock['offer_id']] = ['create' => [
					'oz_prod_id'  => $ozProduct->id(),
				],'update' => [
					'stock'       => 0,
					'sales'       => 0,
				]];
			}

			//$delivering[$ozonStock['offer_id']]['create']['name'] = $ozonStock['name'];

			$delivering[$ozonStock['offer_id']]['update']['stock'] += $quantity;
			$delivering[$ozonStock['offer_id']]['update']['sales'] = $ozonStock['ads'] * 30.4;

			$operations++;
		}

		OzonStock::truncate();
		$ozProdSku = OzonStock::list(['oz_prod_id', 'cluster_id']);
		$ozProdSku->fullUpdate(
			compare: false,
			data: $data,
		)->save();

		$statistic = OzonStatistic::list(['oz_prod_id', 'stock']);
		$statistic->fullUpdate(
			compare: function ($model, $row) {
				return $model->oz_prod_id == $row['create']['oz_prod_id'];
			},
			data: $delivering,
		)->save();

		return [
			'processed' => $operations,
			'resolve'   => true,
		];
	}

	public function updateOzonClusters($options = [])
	{
		$resp = $this->ozon()->send('v1/cluster/list', ['cluster_type' => 'CLUSTER_TYPE_CIS']);
		foreach ($resp['clusters'] as $cluster) {
			$data[] = ['create' => [
				'ozon_id'  => $cluster['id'],
				'name'     => $cluster['name'],
				'russia'   => false,
			]];
		}
		$resp = $this->ozon()->send('v1/cluster/list', ['cluster_type' => 'CLUSTER_TYPE_OZON']);
		foreach ($resp['clusters'] as $cluster) {
			$data[] = ['create' => [
				'ozon_id'  => $cluster['id'],
				'name'     => $cluster['name'],
				'russia'   => true,
			]];
		}

		$ozProdSku = OzonClusters::list(['ozon_id']);
		$ozProdSku->fullUpdate(
			compare: function ($model, $row) {
				return $model->ozon_id == $row['create']['ozon_id'];
			},
			data: $data,
			update: false,
			delete: false
		)->save();

		return [
			'processed' => count($data),
			'resolve'   => true,
		];
	}

	protected function response(#[ArrayShape(['processed' => 'int', 'status' => 'string', 'continue' => 'bool',
		'info' => 'array', 'warnings' => 'array', 'errors' => 'array'])] $resp)
	{
		return array_merge([
			'processed' => 0,
			'status'    => 'success',
			'warnings'  => [],
			'errors'    => [],
			'info'      => [],
			'resolve'   => false,
		], $resp);
	}

	public function cache(): \Pipe\Cache\CacheController
	{
		return $this->sm('cache');
	}


	/**
	 * @return Ozon
	 */
	protected function ozon()
	{
		return $this->sm()->ozon();
	}
}