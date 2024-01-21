<?php // $ bin/console make:command liveTrades:connect

namespace App\Command;

use App\Service\LiveTradesClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'liveTrades:client',
    description: 'Live trades WebSocket client. Subscribe for messages from "' . \App\Config::LiveTradesUrl . '".' . PHP_EOL
    . '  Check the connection: $ npm exec wscat -- -P -c \'' . \App\Config::LiveTradesUrl . '\' -w 10 -x \'' . \App\Config::LiveTradesSubscribe[0] . '\'',
)]
class LiveTradesClientCommand extends Command
{
    public function __construct(
        private readonly LiveTradesClient $liveTradesClient,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        //$this
        //    ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
        //    ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        //;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //$io = new SymfonyStyle($input, $output);
        //$argument = $input->getArgument('argument');
        //if ($argument) $io->note(sprintf('You passed an argument: %s', $argument));
        //if ($input->getOption('option')) $io->error('Optional OOPS!');
        //if (!$output instanceof \Symfony\Component\Console\Output\ConsoleOutputInterface)
        //    $output->writeln('...writeln...');
        //else {
        //    $section = $output->section();
        //    $section->write('...write...');
        //    for ($i = 1; $i <= 3; $i++) $section->write(' ' . $i) || sleep(1);
        //    $section->overwrite('...writeln...');
        //}
        //$io->success('You have a new command! Now make it your own! Pass --help to see your options.');


        $this->liveTradesClient->execute($output);
        return Command::SUCCESS;
    }
}
