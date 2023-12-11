<?php

namespace App\Client;

use tuyapiphp\TuyaApi;

class TuyaClient
{
    private ?string $token = null;
    private ?int $tokenExpiration = null;

    public function __construct(
        private readonly string $accessId,
        private readonly string $secretKey,
        private readonly string $deviceId
    ) {
    }

    public function postCommands(array $commands): void
    {
        $this->validateToken();
        $tuya = $this->getTuyaApi();
        $tuya->devices($this->token)->post_commands($this->deviceId, ['commands' => $commands]);
    }

    public function deviceStatus()
    {
        $this->validateToken();
        $tuya = $this->getTuyaApi();

        return $tuya->devices($this->token)->get_status($this->deviceId);
    }

    private function getTuyaApi(): TuyaApi
    {
        $config = [
            'accessKey' => $this->accessId,
            'secretKey' => $this->secretKey,
            'baseUrl'   => 'https://openapi.tuyaeu.com',
        ];

        return new TuyaApi($config);
    }

    private function setToken(): void
    {
        $tuya     = $this->getTuyaApi();
        $response = $tuya->token->get_new();

        if (isset($response->result->access_token, $response->result->expire_time)) {
            $this->token           = $response->result->access_token;
            $this->tokenExpiration = time() + $response->result->expire_time;

            file_put_contents(
                'token.json',
                json_encode([
                    'token'      => $this->token,
                    'expiration' => $this->tokenExpiration,
                ])
            );
        } else {
            throw new \Exception("Failed to retrieve token.");
        }
    }

    private function validateToken(): void
    {
        if (file_exists('token.json')) {
            $data                  = json_decode(file_get_contents('token.json'), true);
            $this->token           = $data['token'] ?? null;
            $this->tokenExpiration = $data['expiration'] ?? null;
        }

        if ($this->token === null || time() >= $this->tokenExpiration) {
            $this->setToken();
        }
    }
}