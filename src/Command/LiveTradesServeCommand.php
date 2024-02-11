<?php // $ bin/console make:command liveTrades:serve

namespace App\Command;

use App\Classes\LiveTradesCommandBase;
use App\Config;
use App\Service\LiveTradesClient;
use App\Service\LiveTradesConsume;
use App\Service\LiveTradesServe;
//use React\EventLoop\Loop;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'liveTrades:serve',
    description: 'Live trades WebSocket server. See command "liveTrades:client"',
)]
final class LiveTradesServeCommand extends LiveTradesCommandBase
{
    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly LiveTradesConsume $liveTradesConsume,
        private readonly LiveTradesClient $liveTradesClient,
        private readonly LiveTradesServe $liveTradesServe,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('serverOnly', 's', InputOption::VALUE_NONE, 'WebSocket server only (without client, without messages)')
            ->addOption('withClient', 'c', InputOption::VALUE_NONE, 'Run with embedded Client (without messenger transport "' . Config::LiveTradesTransport . '")')
            ->addArgument('port', InputArgument::OPTIONAL, '', $this->params->get('liveTradesPort'),
                [8002, 80])
            ->addArgument('listen', InputArgument::OPTIONAL, '', $this->params->get('liveTradesListen'),
                ['10.0.2.100', '127.0.0.1', '0.0.0.0'])
            ->addArgument('url', InputArgument::OPTIONAL, '', Config::LiveTradesUrl)
            //->addArgument('subscribe', InputArgument::OPTIONAL, '', Config::LiveTradesSubscribe)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $serverOnly = $input->getOption('serverOnly');
        $withMessenger = !$input->getOption('withClient');

        if ($withMessenger)
            $this->liveTradesConsume->init($this->writeln(...));
        else {
            $url = $input->getArgument('url');
            $subscribe = Config::LiveTradesSubscribe;
            $this->liveTradesClient->init(false, $url, $subscribe, $this->output->isVerbose(), $this->write(...), $this->writeln(...));
        }

        $port = $input->getArgument('port');
        $address = $input->getArgument('listen');
        $this->liveTradesServe->init($port, $address, $this->output->isVeryVerbose(), $this->writeln(...));

        if (!$serverOnly && $withMessenger) {
            if (!$this->liveTradesConsume->run()) return Command::FAILURE;
        } else if (!$serverOnly)
            $this->liveTradesClient->run();

        $this->liveTradesServe->run();
        //Loop::get()->addPeriodicTimer(0, fn() => $this->loop->stop()); // test
        return Command::SUCCESS;
    }
}
