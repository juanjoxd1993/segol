<?php

namespace App\Endpoints;

interface ClientEndpointInterface
{
	public function getInfoByRuc(string $ruc);
}