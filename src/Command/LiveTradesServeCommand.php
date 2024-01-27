<?php // $ bin/console make:command liveTrades:serve

namespace App\Command;

use App\Config;
use App\Service\LiveTradesClient;
use App\Service\LiveTradesServe;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'liveTrades:serve',
    description: 'Live trades WebSocket server. See command "liveTrades:client"',
)]
final class LiveTradesServeCommand extends LiveTradesCommandBase
{
    public function __construct(
        private readonly LiveTradesClient $liveTradesClient,
        private readonly LiveTradesServe $liveTradesServe,
        private readonly ParameterBagInterface $params,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::OPTIONAL, '', Config::LiveTradesUrl)
            //->addArgument('subscribe', InputArgument::OPTIONAL, '', Config::LiveTradesSubscribe)
            ->addArgument('port', InputArgument::OPTIONAL, '', $this->params->get('liveTradesPort'),
                [8002, 80])
            ->addArgument('listen', InputArgument::OPTIONAL, '', $this->params->get('liveTradesListen'),
                ['10.0.2.100', '127.0.0.1', '0.0.0.0'])
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $url = $input->getArgument('url');
        $subscribe = Config::LiveTradesSubscribe;
        $this->liveTradesClient->init($url, $subscribe, $this->output->isVerbose(), $this->write(...), $this->writeln(...));

        $port = $input->getArgument('port');
        $address = $input->getArgument('listen');
        $this->liveTradesServe->init($port, $address, $this->output->isVeryVerbose(), $this->writeln(...));

        $this->liveTradesClient->run();
        $this->liveTradesServe->run();
        return Command::SUCCESS;
    }
}
