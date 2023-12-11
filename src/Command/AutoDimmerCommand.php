<?php

namespace App\Command;

use App\Client\TuyaClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sleep;

#[AsCommand(
    name: 'app:auto-dimmer',
    description: 'Add a short description for your command',
)]
class AutoDimmerCommand extends Command
{
    private TuyaClient $tuyaClient;

    public function __construct(TuyaClient $tuyaClient)
    {
        parent::__construct();

        $this->tuyaClient = $tuyaClient;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io   = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $this->tuyaClient->setToken();

        $dimmingTimeInSeconds = 8 * 60; // 8 minutes
        $finalValue           = 255;
        $intervalDuration     = 2;
        $currentValue         = 25;
        $previousValue        = $currentValue;

        $intervals  = $dimmingTimeInSeconds / $intervalDuration;
        $growthRate = pow($finalValue / $currentValue, 1 / $intervals);

        for ($i = $currentValue; $i <= $finalValue;) {
            $i = $i * $growthRate;

            $currentValue = floor($i);

            if ($previousValue !== $currentValue) {
                $previousValue = $currentValue;
                $io->writeln(sprintf('Dimming to %s', $currentValue));

                $this->tuyaClient->postCommands([
                    ['code' => 'bright_value', 'value' => $currentValue],
                ]);
            }

            sleep($intervalDuration);
        }

        $io->success('The sun is up ☀️');

        return Command::SUCCESS;
    }
}
