<?php

namespace App\Service;

use App\Config;
use App\Modules\LiveTrades\LiveTradesEvent;
use App\Modules\LiveTrades\LiveTradesMessage;
use Closure;
use Exception;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Throwable;

final class LiveTradesClient
{
    private LoopInterface $loop;
    private int $messageCount = 0;
    private int $tradesSave = 0;

    /** @param $subscribe string[]
     * @param $writeCb ?Closure(string $message, bool $prefix): void
     * @param $writelnCb ?Closure(string $message): void
     * @noinspection PhpDocSignatureIsNotCompleteInspection */
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly EventDispatcherInterface $eventDispatcher,
        private bool $withMessenger = false,
        private string $url = '',
        private array $subscribe = [],
        private bool $verbose = true,
        private ?Closure $writeCb = null,
        private ?Closure $writelnCb = null,
    )
    {
        $this->loop = Loop::get();
    }

    public function dispatchEmptyMessage(): void
    {
        $this->messageBus->dispatch(new LiveTradesMessage([]), [new TransportNamesStamp(Config::LiveTradesTransport)]);
    }

    /** @param $subscribe string[]
     * @param $write ?Closure(string $message, bool $prefix): void
     * @param $writeln ?Closure(string $message): void
     * @noinspection PhpDocSignatureIsNotCompleteInspection */
    public function init(
        bool $withMessenger,
        string $url = '',
        array $subscribe = [],
        bool $verbose = true,
        ?Closure $write = null,
        ?Closure $writeln = null,
    ): void
    {
        $this->withMessenger = $withMessenger;
        $this->url = $url ?: Config::LiveTradesUrl;
        $this->subscribe = $subscribe ?: Config::LiveTradesSubscribe;
        $this->writeCb = $write;
        $this->writelnCb = $writeln;
        $this->verbose = $verbose;
    }

    public function run(): void {
        $this->messageCount = 0;

        $connector = new Connector($this->loop); //, new \React\Socket\Connector(['dns' => '8.8.8.8', 'timeout' => 10], $this->loop),
        $connector($this->url) //, ['protocol1', 'subprotocol2'], ['Origin' => 'http://localhost'],
        ->then($this->onFulfilled(...), $this->onRejected(...));
    }


    //---

    private function write(string $message, bool $prefix): void
    {
        if ($this->writeCb) ($this->writeCb)($message, $prefix);
    }

    private function writeln(string $message = ''): void
    {
        if ($this->writelnCb) ($this->writelnCb)($message);
    }

    private function getTrades(string $message): array
    {
        // messages:
        // [ 12430, [ [   '1494734166-tBTCUSD', 1705081553, 43535, 0.0156206 ] ] ]
        // [ 12430, 'te', '1494734166-tBTCUSD', 1705081553, 43535, 0.0156206 ]
        $trades = [];
        foreach (Config::LiveTradesSymbol as $symbol) $trades[$symbol] = [];
        $json = json_decode($message);
        if (!is_array($json))
            return $trades;
        else if (is_array($json[1])) {
            $events = $json[1];
        } else if ($json[1] === 'te') {
            array_shift($json);
            array_shift($json);
            $events = [$json];
        } else
            return $trades;

        foreach($events as $event) {
            $id = explode('-', $event[0]);
            $symbol = ltrim($id[1], 't');
            if (isset($trades[$symbol])) $trades[$symbol][] = $event;
        }
        return $trades; // [ 'BTCUSD' => [ '1494734166-tBTCUSD', 1705081553, 43535, 0.0156206 ] ]
    }

    private function sendSubscribe($conn): void {
        foreach($this->subscribe as $subscribe) {
            $this->writeln('[' . $this->url . ' <] ' . $subscribe);
            $conn->send($subscribe);
        }
    }

    private function message(MessageInterface $msg, WebSocket $conn): void {
        if ($this->verbose) $this->writeln('[' . $this->url . ' >] ' . $msg);
        else if ($this->messageCount && $this->messageCount < 100) $this->write('.', false); // feedback: progress

        $this->messageCount++;
        $message = $msg->getPayload();
        $trades = $this->getTrades($message);
        if (!$this->withMessenger) $this->eventDispatcher->dispatch(new LiveTradesEvent($trades));
        else $this->messageBus->dispatch(new LiveTradesMessage($trades), [new TransportNamesStamp(Config::LiveTradesTransport)]);
        foreach ($trades as $events) $this->tradesSave += count($events);

        if (!$this->verbose && $this->messageCount
            && ($this->messageCount === 100 || $this->messageCount % 1000 === 0)) { // feedback: summary
            $log = '[messages] ' . $this->messageCount . ' (saved: ' . $this->tradesSave . '), memory: ' . round(memory_get_usage() / 1024 / 1024) . ' MB ';
            $this->writeln($log); //$this->writeln(); $this->write($log, true);
        }

        if ($this->messageCount === 1) $this->loop->addTimer(1, fn() => $this->sendSubscribe($conn));
        //if ($this->messageCount > 9) $conn->close(); // test
    }

    private function close($code = null, $reason = null): void {
        $this->writeln('[' . $this->url . ' close] (' . $code . ') ' . $reason);
        if ($code !== 1000) $this->loop->addTimer(3, $this->run(...));
    }

    private function onFulfilled(WebSocket $conn): void {
        $this->writeln('[' . $this->url . ' open]');
        $conn->on('message', function($msg) use ($conn) {
            try {
                $this->message($msg, $conn);
            } catch (Throwable $ex) {
                $this->loop->stop();
                $this->writeln('[' . $this->url . ' ERROR] ' . $this->verbose ? $ex : $ex->getMessage());
                throw $ex;
            }
        });
        $conn->on('close', fn($code, $reason) => $this->close($code, $reason));
    }

    private function onRejected(Exception $e): void {
        $this->writeln('[' . $this->url . ' error] ' . $this->verbose ? $e : $e->getMessage());
        //$this->writeln(print_r($e, true));
        //$this->loop->stop();
    }
}
