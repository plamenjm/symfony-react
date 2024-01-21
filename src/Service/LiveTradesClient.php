<?php

namespace App\Service;

use Closure;
use Exception;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Socket\Connector as SocketConnector;
use function Ratchet\Client\connect;

class LiveTradesClient
{
    /** @var $writeCb ?Closure(string $msg, bool $prefix): void */
    private ?Closure $writeCb = null;

    /** @var $writelnCb ?Closure(string $msg): void */
    private ?Closure $writelnCb = null;

    private ?LoopInterface $loop = null;
    private bool $verbose = true;

    /** @var $onMessage ?Closure(string $msg): int */
    private ?Closure $onMessage = null;

    private int $messagesCount = 0;
    private int $messagesSave = 0;

    /** @param $write ?Closure(string $msg, bool $prefix): void
     * @param $writeln ?Closure(string $msg): void
     * @param $onMessage ?Closure(string $msg): int
     * @noinspection PhpDocSignatureIsNotCompleteInspection */
    public function execute(
        ?Closure $write = null,
        ?Closure $writeln = null,
        ?Closure $onMessage = null,
        ?LoopInterface $loop = null,
        bool $verbose = true,
    ): void
    {
        $this->writeCb = $write;
        $this->writelnCb = $writeln;
        $this->onMessage = $onMessage;
        $this->loop = $loop ?: Loop::get();
        $this->verbose = $verbose;
        $this->connect();
    }

    private function write(string $msg, bool $prefix): void
    {
        if ($this->writeCb) ($this->writeCb)($msg, $prefix);
    }

    private function writeln(string $msg = ''): void
    {
        if ($this->writelnCb) ($this->writelnCb)($msg);
    }

    private function connect(): void {
        $this->messagesCount = 0;

        if (!$this->loop)
            connect(\App\Config::LiveTradesUrl)->then($this->onFulfilled(...), $this->onRejected(...));
        else {
            $connector = new Connector($this->loop, new SocketConnector(
                //['dns' => '8.8.8.8', 'timeout' => 10]
            ));
            $promise = $connector(\App\Config::LiveTradesUrl
                //, ['protocol1', 'subprotocol2'], ['Origin' => 'http://localhost']
            );
            $promise->then($this->onFulfilled(...), $this->onRejected(...));
        }
    }

    private function subscribe($conn): void {
        forEach(\App\Config::LiveTradesSubscribe as $subscribe) {
            $this->writeln('[' . \App\Config::LiveTradesUrl . ' <] ' . $subscribe);
            $conn->send($subscribe);
        }
    }

    private function message(MessageInterface $msg, WebSocket $conn): void {
        if ($this->verbose) $this->writeln('[' . \App\Config::LiveTradesUrl . ' >] ' . $msg);
        else if ($this->messagesCount && $this->messagesCount < 100) $this->write('.', false); // feedback: progress

        $this->messagesCount++;
        if ($this->onMessage) {
            $count = ($this->onMessage)($msg->getPayload());
            if ($count) $this->messagesSave += $count;
        }

        if (!$this->verbose && $this->messagesCount
            && ($this->messagesCount === 100 || $this->messagesCount % 1000 === 0)) { // feedback: summary
            $this->writeln();
            $this->write('[messages] ' . $this->messagesSave . '/' . $this->messagesCount . ' ' . round(memory_get_usage() / 1024 / 1024) . ' MB ', true);
        }

        if ($this->messagesCount === 1) {
            if ($this->loop) $this->loop->addTimer(1, fn() => $this->subscribe($conn));
            else $this->subscribe($conn);
        }
        //if ($this->messagesCount > 9) $conn->close(); // test
    }

    private function close($code = null, $reason = null): void {
        $this->writeln('[' . \App\Config::LiveTradesUrl . ' close] (' . $code . ') ' . $reason);
        if ($code !== 1000) {
            if (!$this->loop) $this->connect();
            else $this->loop->addTimer(3, $this->connect(...));
        }
    }

    private function onFulfilled(WebSocket $conn): void {
        $this->writeln('[' . \App\Config::LiveTradesUrl . ' open]');
        $conn->on('message', fn($msg) => $this->message($msg, $conn));
        $conn->on('close', fn($code, $reason) => $this->close($code, $reason));
    }

    private function onRejected(Exception $e): void {
        $this->writeln('[' . \App\Config::LiveTradesUrl . ' error] ' . $e->getMessage());
        //$this->writeln(print_r($e, true));
        $this->loop?->stop();
    }
}
