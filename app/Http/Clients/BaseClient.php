<?php

namespace App\Clients;

use GuzzleHttp\Client;

class BaseClient
{
    protected $httpClient;

    public function __construct($baseUri)
    {
        $this->httpClient = new Client([
            'base_uri' => $baseUri
        ]);
    }

    protected function request($method, $url, $options = [])
    {
        try {
            $response = $this->httpClient->request($method, $url, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            throw new \Exception("Error al hacer la solicitud: " . $e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            throw new \Exception("Error desconocido: " . $e->getMessage() . " = " . $e->getCode());
        }
    }
}
