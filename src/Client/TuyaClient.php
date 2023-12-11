<?php

namespace App\Client;

use tuyapiphp\TuyaApi;

class TuyaClient
{

    private string $token;

    public function __construct(
        private readonly string $accessId,
        private readonly string $secretKey,
        private readonly string $deviceId
    ) {
    }

    public function setToken()
    {
        $config
            = [
            'accessKey' => $this->accessId,
            'secretKey' => $this->secretKey,
            'baseUrl'   => 'https://openapi.tuyaeu.com',
        ];

        $tuya = new TuyaApi($config);

        $this->token = $tuya->token->get_new()->result->access_token; // @todo[m]: store and reuse
    }

    public function postCommands(array $commands)
    {
        $config
            = [
            'accessKey' => $this->accessId,
            'secretKey' => $this->secretKey,
            'baseUrl'   => 'https://openapi.tuyaeu.com',
        ];

        $tuya = new TuyaApi($config);

        $tuya
            ->devices($this->token)
            ->post_commands($this->deviceId, ['commands' => $commands]);
    }

    public function deviceStatus()
    {
        $config
            = [
            'accessKey' => $this->accessId,
            'secretKey' => $this->secretKey,
            'baseUrl'   => 'https://openapi.tuyaeu.com',
        ];

        $tuya = new TuyaApi($config);

        return $tuya
            ->devices($this->token)
            ->get_status($this->deviceId);
    }
}