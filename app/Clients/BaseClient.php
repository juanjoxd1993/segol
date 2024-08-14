<?php

namespace App\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class BaseClient
{
    protected $httpClient;

    public function __construct(string $baseUri)
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
            // Aquí puedes manejar errores específicos de Guzzle, como timeouts, conexiones fallidas, etc.
            throw new \Exception("Error al hacer la solicitud: " . $e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            // Aquí puedes manejar otros tipos de errores o lanzar una excepción personalizada
            throw new \Exception("Error desconocido: " . $e->getMessage(), $e->getCode());
        }
    }
}
