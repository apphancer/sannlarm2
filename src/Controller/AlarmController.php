<?php

namespace App\Controller;

use App\Service\AlarmService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlarmController extends AbstractController
{
    #[Route('/alarm', name: 'app_alarm')]
    public function index(AlarmService $alarmService): Response
    {
        $currentAlarm = $alarmService->getAlarm();
        $isRinging    = $alarmService->isActivated();

        return $this->render('alarm/index.html.twig', [
            'currentAlarm' => $currentAlarm,
            'isRinging'    => $isRinging,
        ]);
    }

    #[Route('/alarm/set', name: 'app_alarm_set')]
    public function set(AlarmService $alarmService): Response
    {
        $time = '15:00';

        $alarmService->setAlarm($time);

        dd('Alarm set for '.$time);
    }
}
