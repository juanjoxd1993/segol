<?php

namespace App\Clients;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class EfactClient extends BaseClient
{
    public const EFACT_CLIENT_AUTH_TOKEN = 'efact_client_auth_token';
    public const EFACT_CLIENT_AUTH_TOKEN_TOKEN = 'token';
    public const EFACT_CLIENT_AUTH_TOKEN_EXPIRES_IN = 'expires_in';
    public const EFACT_CLIENT_AUTH_TOKEN_CREATED_AT_TS = 'created_at_ts';
    public const URL_CREATE_TOKEN = 'oauth/token';
    public const URL_SEND_XML = 'v1/document';
    public const URL_GET_XML_FROM_TICKET = 'v1/pdf/%s';
    public const API_EFACT_BASE_URL = 'https://ose.efact.pe/api-efact-ose/';
    public function __construct()
    {
        parent::__construct(env('API_EFACT_BASE_URL'));
    }

    public function getAccessToken()
    {
        if (!$this->isTokenValid()) {
            $response = $this->request('POST', self::URL_CREATE_TOKEN, [
                'form_params' => [
                    'username' => env('API_EFACT_USERNAME'),
                    'password' => env('API_EFACT_PASSWORD'),
                    'grant_type' => 'password'
                ],
                'headers' => [
                    'Authorization' => 'Basic ' . env('API_EFACT_AUTH_BASIC_KEY')
                ]
            ]);

            $this->storeTokenInSession($response);
        }

        return $this->getTokenValueFromSession();
    }

    public function sendDocumentXML(string $documentPath)
    {
        try {
            $response = $this->request('POST', self::URL_SEND_XML, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken()
                ],
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($documentPath, 'r'),
                        'filename' => basename($documentPath),
                    ],
                ]
            ]);
            return $response;
        } catch (RequestException $e) {
            // Registra el error utilizando el facade de logging de Laravel
            Log::error('Error al enviar el archivo XML', ['error' => $e->getMessage()]);

            // Lanza una excepción personalizada o retorna un mensaje de error adecuado
            throw new \Exception('Error al enviar el archivo XML: ' . $e->getMessage());
        }
    }

    public function getXmlFromTicket(string $ticket)
    {
        $response = $this->request('GET', sprintf(self::URL_GET_XML_FROM_TICKET, $ticket), [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAccessToken()
            ]
        ]);

        return $response;
    }

    private function storeTokenInSession(array $response)
    {


        $data = [
            self::EFACT_CLIENT_AUTH_TOKEN_TOKEN => $response['access_token'],
            self::EFACT_CLIENT_AUTH_TOKEN_EXPIRES_IN => $response['expires_in'],
            self::EFACT_CLIENT_AUTH_TOKEN_CREATED_AT_TS => now()->getTimestamp()
        ];

        session([self::EFACT_CLIENT_AUTH_TOKEN =>  $data]);
    }

    private function isTokenValid(): bool
    {
        if (!session()->exists(self::EFACT_CLIENT_AUTH_TOKEN)) {
            return false;
        }

        $currentTs = now()->getTimestamp();
        $tokenData = session(self::EFACT_CLIENT_AUTH_TOKEN);
        $expirationTs = $tokenData[self::EFACT_CLIENT_AUTH_TOKEN_CREATED_AT_TS] + $tokenData[self::EFACT_CLIENT_AUTH_TOKEN_EXPIRES_IN];

        if ($expirationTs >= $currentTs) {
            return false;
        }

        return true;
    }

    private function getTokenValueFromSession(): string
    {

        return session(self::EFACT_CLIENT_AUTH_TOKEN)[self::EFACT_CLIENT_AUTH_TOKEN_TOKEN];
    }

    public function getTokenRemainingTime(): int
    {
        if (!session()->exists(self::EFACT_CLIENT_AUTH_TOKEN)) {
            return 0;
        }

        $currentTs = now()->getTimestamp();
        $tokenData = session(self::EFACT_CLIENT_AUTH_TOKEN);
        $expirationTs = $tokenData[self::EFACT_CLIENT_AUTH_TOKEN_CREATED_AT_TS] + $tokenData[self::EFACT_CLIENT_AUTH_TOKEN_EXPIRES_IN];

        $remainingTime = $expirationTs - $currentTs;

        return $remainingTime > 0 ? $remainingTime : 0;
    }
}
