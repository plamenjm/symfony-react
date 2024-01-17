<?php // $ bin/console make:command live:connect

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
//use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'live:client',
    description: 'Connects to "' . self::WEBSOCKET . '" and subscribe.' . PHP_EOL
    . '  Check the connection: $ npm exec wscat -- -P -c \'' . self::WEBSOCKET . '\' -w 10 -x \'' . self::SUBSCRIBE[0] . '\'',
)]
class LiveClientCommand extends Command
{
    public const WEBSOCKET = 'wss://api.bitfinex.com/ws/1';
    public const SUBSCRIBE = [
        '{"event": "subscribe", "channel": "trades", "pair": "BTCUSD"}',
        '{"event": "subscribe", "channel": "trades", "pair": "BTCEUR"}',
    ];

    private ?\React\EventLoop\LoopInterface $loop = null;
    private ?\Closure $onMessage = null;
    private OutputInterface $output;
    private int $msgCount = 0;


    //---

    public function __construct()
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



        if (!$this->loop) $this->loop = \React\EventLoop\Loop::get();
        $this->output = $output;
        $this->connect();

        return Command::SUCCESS;
    }

    /** @noinspection PhpDocSignatureInspection PhpFullyQualifiedNameUsageInspection
     * @param $onMessage ?Closure(\Ratchet\RFC6455\Messaging\MessageInterface $msg): void */
    public function executeWithLoop(InputInterface $input, OutputInterface $output, \React\EventLoop\LoopInterface $loop, \Closure $onMessage) {
        $this->loop = $loop;
        $this->onMessage = $onMessage;
        $this->execute($input, $output);
    }


    //--- //??? todo new service

    private function connect() {
        $this->msgCount = 0;

        if (!$this->loop)
            \Ratchet\Client\connect(self::WEBSOCKET)->then($this->onFulfilled(...), $this->onRejected(...));
        else {
            $connector = new \Ratchet\Client\Connector($this->loop, new \React\Socket\Connector(
                //['dns' => '8.8.8.8', 'timeout' => 10]
            ));
            $promise = $connector(self::WEBSOCKET
                //, ['protocol1', 'subprotocol2'], ['Origin' => 'http://localhost']
            );
            $promise->then($this->onFulfilled(...), $this->onRejected(...));
        }
    }

    private function subscribe($conn) {
        forEach(self::SUBSCRIBE as $subscribe) {
            $this->output->writeln('[' . self::WEBSOCKET . ' <] ' . $subscribe);
            $conn->send($subscribe);
        }
    }

    private function message(\Ratchet\RFC6455\Messaging\MessageInterface $msg, \Ratchet\Client\WebSocket $conn) {
        $this->output->writeln('[' . self::WEBSOCKET . ' >] ' . $msg);

        $this->msgCount++;
        if ($this->msgCount === 1) {
            //if (!$this->loop)
                $this->subscribe($conn);
            //else $this->loop->addTimer(1, fn() => $this->subscribe($conn));
        }
        //else if ($this->msgCount > 9) $conn->close(); // test

        if ($this->onMessage) ($this->onMessage)($msg);
    }

    private function close($code = null, $reason = null) {
        $this->output->writeln('[' . self::WEBSOCKET . ' close] (' . $code . ') ' . $reason);
        if ($code !== 1000) {
            if (!$this->loop) $this->connect();
            else $this->loop->addTimer(3, $this->connect(...));
        }
    }

    private function onFulfilled(\Ratchet\Client\WebSocket $conn) {
        $this->output->writeln('[' . self::WEBSOCKET . ' open]');
        $conn->on('message', fn($msg) => $this->message($msg, $conn));
        $conn->on('close', $this->close(...));
    }

    private function onRejected(\Exception $e) {
        $this->output->writeln('[' . self::WEBSOCKET . ' error] ' . $e->getMessage());
        //$this->output->writeln(print_r($e, true));
        $this->loop?->stop();
    }
}
