<?php // $ bin/console make:command liveTrades:serve

namespace App\Command;

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
class LiveTradesServeCommand extends Command
{
    private ?OutputInterface $output = null;
    private bool $needsLn = false;

    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly LiveTradesServe $liveTradesServe,
        private readonly LiveTradesClient $liveTradesClient,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('port', InputArgument::OPTIONAL, '', $this->params->get('liveTradesPort'),
                [8002, 80])
            ->addArgument('listen', InputArgument::OPTIONAL, '', $this->params->get('liveTradesListen'),
                ['10.0.2.100', '127.0.0.1', '0.0.0.0'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $server = $this->liveTradesServe->getServer($input->getArgument('port'), $input->getArgument('listen'));

        $this->liveTradesClient->execute($this->write(...), $this->writeln(...),
            $this->liveTradesServe->messageSaveAndSend(...), $server->loop,
            $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE);
        $this->liveTradesServe->execute($this->write(...), $this->writeln(...),
            $output->getVerbosity() > OutputInterface::VERBOSITY_VERBOSE);

        return Command::SUCCESS;
    }

    private function write(string $line, bool $prefix): void
    {
        $this->output->write((!$prefix ? '' : \App\Utils::dateTimeUTC() . ' ') . $line);
        $this->needsLn = true;
    }

    private function writeln(string $line): void
    {
        if ($this->needsLn) $this->output->writeln('');
        if (!$line) $this->output->writeln('');
        else $this->output->writeln(\App\Utils::dateTimeUTC() . ' ' . $line);
        $this->needsLn = false;
    }
}
