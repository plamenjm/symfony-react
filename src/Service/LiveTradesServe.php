<?php

namespace App\Service;

use Closure;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use SplObjectStorage;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class LiveTradesServe implements MessageComponentInterface
{
    public SplObjectStorage $clients;
    private ?int $port = null;
    private ?string $listen = null;
    private ?IoServer $server;

    /** @var $writelnCb ?Closure(string $msg): void */
    private ?Closure $writelnCb = null;

    private bool $verbose = false;

    private array $messages = [];

    public function __construct(
        private readonly ParameterBagInterface $params,
    )
    {
        $this->clients = new SplObjectStorage;
    }

    public function getServer(?int $port = null, ?string $listen = null): IoServer
    {
        $this->port = $port ?: $this->params->get('liveTradesPort');
        $this->listen = $listen ?: $this->params->get('liveTradesListen');

        $ws = new WsServer($this);
        $http = new HttpServer($ws);
        $this->server = IoServer::factory($http, $this->port, $this->listen);
        return $this->server;
    }

    /** $writeln ?Closure(string $msg): void
     * @noinspection PhpDocSignatureIsNotCompleteInspection */
    public function execute(?Closure $write = null, ?Closure $writeln = null, $verbose = false)
    {
        if (!$this->server) $this->getServer();

        $this->writelnCb = $writeln;
        $this->verbose = $verbose;
        $this->writeln('[ws://' . $this->listen . ':' . $this->port . ' listening]');
        $this->server->run();
    }

    private function writeln(string $msg = ''): void
    {
        if ($this->writelnCb) ($this->writelnCb)($msg);
    }

    public function messageSave(string $message): int
    {
        $json = json_decode($message);
        if (!is_array($json)) return 0;
        if (is_array($json[1])) {
            array_push($this->messages, ...$json[1]);
            return count($json[1]);
        } else if ($json[1] === 'te') {
            array_shift($json);
            array_shift($json);
            $this->messages[] = $json;
            return 1;
        }
        return 0;
    }

    public function messageSend(string $message, int $count): void
    {
        foreach ($this->clients as $client) {
            if ($this->verbose || $count > 1)
                $this->writeln('[' . $client->resourceId . '/' . count($this->clients) . ' <] '
                    . ($count === 1 ? $message : $count . ' messages'));
            $client->send($message);
        }
    }

    public function messageSaveAndSend(string $message): bool
    {
        $count = $this->messageSave($message);
        if ($count) $this->messageSend($message, $count);
        return $count > 0;
    }

    function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $this->writeln('[' . $conn->resourceId . '@' . $conn->remoteAddress . '/' . count($this->clients) . ' open]');
        $this->messageSend(json_encode([0, $this->messages]), count($this->messages));
    }

    function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->writeln('[' . $conn->resourceId . '/' . count($this->clients) . ' close]');
    }

    function onError(ConnectionInterface $conn, Exception $e)
    {
        $this->writeln('[' . ($conn->resourceId ?: '') . '/' . count($this->clients) . ' error] ' . $e->getMessage());
        //$this->writeln(print_r($e, true));
        $conn->close();
        $this->clients->detach($conn);
    }

    function onMessage(ConnectionInterface $from, $msg)
    {
        $this->writeln('[' . $from->resourceId . '/' . count($this->clients) . ' >] ' . $msg);
        if ($this->verbose) $this->writeln('[' . $from->resourceId . '/' . count($this->clients) . ' <] ' . $msg);
        $from->send($msg);
    }
}
