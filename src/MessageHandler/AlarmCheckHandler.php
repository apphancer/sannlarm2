<?php

namespace App\MessageHandler;

use App\Client\TuyaClient;
use App\Message\AlarmCheck;
use App\Service\AlarmService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly final class AlarmCheckHandler
{

    public function __construct(private readonly AlarmService $alarmService, private readonly TuyaClient $tuyaClient)
    {
    }

    public function __invoke(AlarmCheck $alarmCheck): void
    {
        if ($this->alarmService->isActivated()) {
            dump('Alarm Activated!');
            $this->tuyaClient->dimLight();
        }
    }
}