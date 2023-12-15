<?php

namespace App\Service;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use tuyapiphp\TuyaApi;

use function floor;
use function pow;
use function sleep;
use function sprintf;

class TuyaService
{
    private ?string $token = null;
    private ?int $tokenExpiration = null;
    private string $tokenFilePath;
    private Filesystem $filesystem;


    public function __construct(
        private readonly string $accessId,
        private readonly string $secretKey,
        private readonly string $deviceId,
        private readonly int $dimmingTimeSeconds,
        private readonly KernelInterface $appKernel
    ) {
        $this->tokenFilePath = $this->appKernel->getProjectDir().'/var/token.json';
        $this->filesystem    = new Filesystem();
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

    public function dimLight(SymfonyStyle $io = null): void
    {
        $minValue         = 25;
        $maxValue         = 255;
        $intervalDuration = 2;
        $previousValue    = $minValue;

        $intervals  = $this->dimmingTimeSeconds / $intervalDuration;
        $growthRate = pow($maxValue / $minValue, 1 / $intervals);

        $this->postCommands([
            ['code' => 'bright_value', 'value' => $minValue],
            ['code' => 'switch_led', 'value' => true],
        ]);

        for ($i = $minValue; $i <= $maxValue;) {
            $i = $i * $growthRate;

            $minValue = floor($i);

            if ($previousValue !== $minValue) {
                $previousValue = $minValue;

                $output = sprintf('%s: %s', date('H:i:s'), $minValue);

                if (null !== $io) {
                    $io->writeln($output);
                } else {
                    dump($output);
                }

                $this->postCommands([
                    ['code' => 'bright_value', 'value' => $minValue],
                ]);
            } else {
                $output = sprintf('%s: %s', date('H:i:s'), null);

                if (null !== $io) {
                    $io->writeln($output);
                } else {
                    dump($output);
                }
            }

            sleep($intervalDuration);
        }
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

            $this->filesystem->dumpFile(
                $this->tokenFilePath,
                json_encode(['token' => $this->token, 'expiration' => $this->tokenExpiration])
            );
        } else {
            throw new \Exception("Failed to retrieve token.");
        }
    }

    private function validateToken(): void
    {
        if ($this->filesystem->exists($this->tokenFilePath)) {
            $data                  = json_decode(file_get_contents($this->tokenFilePath), true);
            $this->token           = $data['token'] ?? null;
            $this->tokenExpiration = $data['expiration'] ?? null;
        }

        if ($this->token === null || time() >= $this->tokenExpiration) {
            $this->setToken();
        }
    }
}