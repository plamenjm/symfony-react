<?php // $ bin/console make:command live:serve

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'live:serve',
    description: 'WebSocket server. See command "live:client"',
)]
class LiveServeCommand extends Command implements \Ratchet\MessageComponentInterface
{
    public const LISTEN = '10.0.2.100'; //'127.0.0.1'; //'0.0.0.0';
    public const PORT = 8002;


    //---

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        //$this
        //    ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        //    ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        //;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //$io = new SymfonyStyle($input, $output);
        //$arg1 = $input->getArgument('arg1');
        //if ($arg1) $io->note(sprintf('You passed an argument: %s', $arg1));
        //if ($input->getOption('option1')) {}
        //$io->success('You have a new command! Now make it your own! Pass --help to see your options.');



        $messageComponentInterface = new LiveServeCommand;
        $messageComponentInterface->output = $output;
        $messageComponentInterface->clients = new \SplObjectStorage;
        $ws = new \Ratchet\WebSocket\WsServer($messageComponentInterface);
        $http = new \Ratchet\Http\HttpServer($ws);
        $server = \Ratchet\Server\IoServer::factory($http, self::PORT, self::LISTEN);



        $client = new \App\Command\LiveClientCommand();
        $client->executeWithLoop($input, $output, $server->loop, $messageComponentInterface->message(...));
        $output->writeln('[ws://' . self::LISTEN . ':' . self::PORT . ' listening]');
        $server->run();

        return Command::SUCCESS;
    }


    //--- //??? todo new service

    private OutputInterface $output;
    private \SplObjectStorage $clients;

    function onOpen(\Ratchet\ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $this->output->writeln('[' . $conn->resourceId . '@' . $conn->remoteAddress . '/' . count($this->clients) . ' open]');
    }

    function onClose(\Ratchet\ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->output->writeln('[' . $conn->resourceId . '/' . count($this->clients) . ' close]');
    }

    function onError(\Ratchet\ConnectionInterface $conn, \Exception $e)
    {
        $client = $conn->resourceId ? $conn->resourceId : '';
        $this->output->writeln('[' . $client . '/' . count($this->clients) . ' error] ' . $e->getMessage());
        //$this->output->writeln(print_r($e, true));
        $conn->close();
        $this->clients->detach($conn);
    }

    function onMessage(\Ratchet\ConnectionInterface $from, $msg)
    {
        $this->output->writeln('[' . $from->resourceId . '/' . count($this->clients) . ' >] ' . $msg);

        $this->output->writeln('[' . $from->resourceId . '/' . count($this->clients) . ' <] ' . $msg);
        $from->send($msg);
    }

    private function message(\Ratchet\RFC6455\Messaging\MessageInterface $msg)
    {
        foreach ($this->clients as $client) {
            //$this->output->writeln('[' . $client->resourceId . '/' . count($this->clients) . ' <] ' . $msg);
            $client->send($msg);
        }
    }
}
