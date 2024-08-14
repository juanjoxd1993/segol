<?php

namespace App\Http\Clients;

use GuzzleHttp\Client;

class CompanyClient extends Client
{
	const VERSION = 'v1';

	private $client;

	public function __construct()
	{
		parent::__construct([
			'base_uri' => env('API_RUC_BASE_URL') . self::VERSION . '/',
		]);
	}
}