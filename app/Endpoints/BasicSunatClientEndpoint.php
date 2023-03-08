<?php

namespace App\Endpoints;

use App\Http\Clients\CompanyClient;

class BasicSunatClientEndpoint implements ClientEndpointInterface
{
	const CONSULTAS = 'consultas';

	private $companyHttpClient;

	public function __construct(CompanyClient $companyHttpClient)
	{
		$this->companyHttpClient = $companyHttpClient;
	}

	public function getInfoByRuc(string $ruc)
	{
		$res = $this->companyHttpClient->request('POST', self::CONSULTAS, [
			'json' => [
				'token' => env('API_RUC_TOKEN'),
				'ruc' => $ruc,
			],
		]);

		$responseBody = $res->getBody()->getContents();

		return $responseBody;
	}
}