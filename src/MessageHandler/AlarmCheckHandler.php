<?php

namespace App\MessageHandler;

use App\Message\AlarmCheck;
use App\Service\AlarmService;
use App\Service\TuyaService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly final class AlarmCheckHandler
{

    public function __construct(private readonly AlarmService $alarmService, private readonly TuyaService $tuyaService)
    {
    }

    public function __invoke(AlarmCheck $alarmCheck): void
    {
        dump('Alarm Checked');
        if ($this->alarmService->isActivated()) {
            dump('Alarm Activated!');
            $this->tuyaService->dimLight();
        }
    }
}