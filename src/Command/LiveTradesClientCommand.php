<?php // $ bin/console make:command liveTrades:connect

namespace App\Command;

use App\Classes\LiveTradesCommandBase;
use App\Config;
use App\Service\LiveTradesClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'liveTrades:client',
    description: 'Live trades WebSocket client. Subscribe for messages from "' . Config::LiveTradesUrl . '".' . PHP_EOL
    . '  Check the connection: $ npm exec wscat -- -P -c \'' . Config::LiveTradesUrl . '\' -w 10 -x \'' . Config::LiveTradesSubscribe[0] . '\'',
)]
final class LiveTradesClientCommand extends LiveTradesCommandBase
{
    public function __construct(
        private readonly LiveTradesClient $liveTradesClient,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('testMessage', 't', InputOption::VALUE_NONE, 'Dispatch a test message to transport "' . Config::LiveTradesTransport . '"')
            ->addArgument('url', InputArgument::OPTIONAL, '', Config::LiveTradesUrl)
            //->addArgument('subscribe', InputArgument::OPTIONAL, '', Config::LiveTradesSubscribe)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        //$io = new SymfonyStyle($input, $output);
        //if ($input->getArgument('url')) $io->note(sprintf('You passed an url: %s', $input->getArgument('url')));
        //if ($input->getOption('testMessage')) $io->error('Optional OOPS!');
        //if (!$output instanceof \Symfony\Component\Console\Output\ConsoleOutputInterface)
        //    $output->writeln('...writeln...');
        //else {
        //    $section = $output->section();
        //    $section->write('...write...');
        //    for ($i = 1; $i <= 3; $i++) $section->write(' ' . $i) || sleep(1);
        //    $section->overwrite('...writeln...');
        //}
        //$io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        parent::execute($input, $output);
        if ($input->getOption('testMessage'))
            $this->liveTradesClient->dispatchEmptyMessage();
        else {
            $url = $input->getArgument('url');
            $subscribe = Config::LiveTradesSubscribe;

            $this->liveTradesClient->init(true, $url, $subscribe, $this->output->isVerbose(), $this->write(...), $this->writeln(...));
            $this->liveTradesClient->run();
        }
        return Command::SUCCESS;
    }
}
