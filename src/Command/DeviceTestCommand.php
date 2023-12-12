<?php

namespace App\Command;

use App\Service\TuyaService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function dd;

#[AsCommand(
    name: 'app:device-test',
    description: 'Test device conditions and data',
)]
class DeviceTestCommand extends Command
{
    public function __construct(private readonly TuyaService $tuyaService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('setting', null, InputOption::VALUE_REQUIRED, '', 'status', [
                'status',
                'autodim',
                'postcommand',
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io   = new SymfonyStyle($input, $output);
        $setting = $input->getOption('setting');

        if ($setting === 'status') {
            dump($this->tuyaService->deviceStatus());
        } elseif ($setting === 'autodim') {
            $io->success('The sun is rising ☀️');
            $this->tuyaService->dimLight($io);
            $io->success('The sun is up ☀️☀️☀️☀️☀️');
        } elseif ($setting === 'postcommand') {
            $commands = [
                ['code' => 'bright_value', 'value' => 25], // min 25, max 255
                //['code' => 'switch_led', 'value' => true], // @todo[m]: turn on/off
                ['code' => 'colour_data', 'value' => '{"h":1.0,"s":255.0,"v":255.0}'], // @todo[m]: turn on
            ];

            $this->tuyaService->postCommands($commands);
        }


        $io->success('Done!');

        return Command::SUCCESS;
    }
}
