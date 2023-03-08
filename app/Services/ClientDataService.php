<?php

namespace App\Services;

use App\Endpoints\ClientEndpointInterface;

class ClientDataService
{
	private $clientEndpoint;

	public function __construct(ClientEndpointInterface $clientEndpoint)
	{
		$this->clientEndpoint = $clientEndpoint;
	}

	public function getClientInfoByRuc(string $ruc): ?array
	{
		$res = (array) json_decode($this->clientEndpoint->getInfoByRuc($ruc));

		return $res['success'] === true ? $res : null;
	}
}