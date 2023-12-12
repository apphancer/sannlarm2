<?php

namespace App\Service;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

use function file_get_contents;
use function json_decode;
use function json_encode;

class AlarmService
{
    private string $alarmDataFilePath;
    private Filesystem $filesystem;

    private string|null $lastActivationTime = null;

    public function __construct(
        private readonly KernelInterface $appKernel
    ) {
        $this->alarmDataFilePath = $this->appKernel->getProjectDir().'/var/alarm.json';
        $this->filesystem        = new Filesystem();
    }

    public function setAlarm(string $time): void
    {
        $this->filesystem->dumpFile(
            $this->alarmDataFilePath,
            json_encode($this->getNextOccurrenceOfTime($time))
        );
    }

    public function getAlarm(): ?string
    {
        if ($this->filesystem->exists($this->alarmDataFilePath)) {
            $data = json_decode(file_get_contents($this->alarmDataFilePath), true);

            return $data['date'];
        }

        return null;
    }


    public function isActivated(): bool
    {
        list($alarmTime, $currentTimeFormatted) = $this->getAlarmDataAndCurrentTime();

        if (null === $alarmTime) {
            return false;
        }

        if ($this->lastActivationTime === $currentTimeFormatted) {
            return false;
        }

        if ($alarmTime->format('Y-m-d H:i') === $currentTimeFormatted) {
            $this->lastActivationTime = $currentTimeFormatted;
            return true;
        }

        return false;
    }

    private function getAlarmDataAndCurrentTime(): array
    {
        if (null === $this->getAlarm()) {
            return [null, null];
        }

        $data = json_decode(file_get_contents($this->alarmDataFilePath), true);
        $alarmTime = new DateTimeImmutable($data['date'], new DateTimeZone($data['timezone']));
        $currentTimeFormatted = (new DateTimeImmutable('now', new DateTimeZone($data['timezone'])))->format('Y-m-d H:i');

        return [$alarmTime, $currentTimeFormatted];
    }

    public function removeAlarm()
    {
    }

    private function getNextOccurrenceOfTime(string $time): DateTimeImmutable
    {
        $now = new DateTimeImmutable();

        [$hours, $minutes] = explode(':', $time);

        $specifiedDateTime = $now->setTime((int)$hours, (int)$minutes);

        if ($now > $specifiedDateTime) {
            return $specifiedDateTime->modify('+1 day');
        }

        return $specifiedDateTime;
    }

}