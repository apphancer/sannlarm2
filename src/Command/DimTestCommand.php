<?php

namespace App\Command;

use App\Service\TuyaService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function dd;

#[AsCommand(
    name: 'app:dim-test',
    description: 'Add a short description for your command',
)]
class DimTestCommand extends Command
{
    public function __construct(private readonly TuyaService $tuyaService)
    {
        parent::__construct();
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

        $deviceStatus = $this->tuyaService->deviceStatus();

        dd($deviceStatus);


        $commands = [
            ['code' => 'bright_value', 'value' => 25], // min 25, max 255
            //['code' => 'switch_led', 'value' => true], // @todo[m]: turn on/off
            ['code' => 'colour_data', 'value' => '{"h":1.0,"s":255.0,"v":255.0}'], // @todo[m]: turn on
        ];

        $result = $this->tuyaService
            ->devices($token)
            ->post_commands($deviceId, ['commands' => $commands]);

        dump($result);


        $io->success('Done!');

        return Command::SUCCESS;
    }
}
