<?php

namespace Pipe\Ozon;

use GuzzleHttp\Client;
use Laminas\Json\Json;

class Ozon
{
	protected string $login = '';
	protected string $password = '';
	protected array $cash = [];

	public function __construct($options)
	{
		$this->login = $options['login'];
		$this->password = $options['password'];
	}

	public function send($path, $params = null)
	{
		$cashKey = md5($path . ' => ' . serialize($params));

		if(isset($this->cash[$cashKey])) {
			return $this->cash[$cashKey];
		}

		$url = 'https://api-seller.ozon.ru/' . ltrim($path, '/');

		$client = new Client();

		$postParams = [
			'headers' => [
				'Client-Id' => $this->login,
				'Api-Key'   => $this->password,
				'Content-Type' => 'application/json',
			],
		];

		if ($params) {
			$postParams['json'] = $params;
		}

		$response = $client->post($url, $postParams);
		$result = Json::decode($response->getBody()->getContents(), 1);

		return $this->cash[$cashKey] = $result;
	}
}