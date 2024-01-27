<?php

namespace App\Controller;

use App\Config;
use Closure;
use Exception;
//use GuzzleHttp\Psr7\Request;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

abstract class LiveTradesControllerBase implements MessageComponentInterface
{
    protected SplObjectStorage $clients;

    protected bool $verbose;

    /** @var Closure(string $message): void */
    private Closure $writelnCb;

    /** @param $subscribed SplObjectStorage[] */
    public function __construct(
        protected array $subscribed = [],
    )
    {
        $this->clients = new SplObjectStorage;
        foreach (Config::LiveTradesSymbol as $symbol) $this->subscribed[$symbol] = new SplObjectStorage;
    }

    /** @param $writelnCb Closure(string $message): void
     * @noinspection PhpDocSignatureIsNotCompleteInspection */
    public function init(bool $verbose, Closure $writelnCb)
    {
        $this->verbose = $verbose;
        $this->writelnCb = $writelnCb;
    }


    //---

    protected function writeln(string $message = '', string $subject = '', ConnectionInterface $conn = null, bool $withAddr = false): void
    {
        if (!$this->writelnCb) return;

        $sub = $subject && $conn ? ' ' . $subject : $subject;

        $addr = $sub && $conn && $withAddr ? '@' . $this->remoteAddress($conn) : '';
        if (!$sub) $pref = '';
        else if (!$conn) $pref = '[' . $sub . ']';
        else $pref = '[' . ($this->resourceId($conn) ?: '') . $addr . '/' . count($this->clients) . $sub . ']';

        $msg = $message ? ' ' . $message : '';

        ($this->writelnCb)($pref . $msg);
    }

    private function resourceId(ConnectionInterface $conn): string
    {
        return $conn->resourceId;
    }

    private function remoteAddress(ConnectionInterface $conn): string
    {
        return $conn->remoteAddress;
    }

    //protected function request(ConnectionInterface $conn): Request
    //{
    //    return $conn->httpRequest;
    //}

    //protected function requestPath(ConnectionInterface $conn): string
    //{
    //    $uri = $this->request($conn)->getUri();
    //    return explode('/', $uri->getPath())[1];
    //}

    //protected function requestQuery(ConnectionInterface $conn): array
    //{
    //    $uri = $this->request($conn)->getUri();
    //    parse_str($uri->getQuery(), $query);
    //    return $query;
    //}

    protected function messageSend(string $message, int $count, SplObjectStorage $clients = null): void
    {
        if (!$clients) $clients = $this->clients;
        if (!$count || !$clients->count()) return;
        foreach ($clients as $client) {
            if ($this->verbose || $count > 1)
                $this->writeln($count === 1 ? $message : $count . ' messages', '<', $client);
            $client->send($message);
        }
    }

    function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $this->writeln('', 'open', $conn);
    }

    function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $this->writeln('', 'close', $conn);
    }

    function onError(ConnectionInterface $conn, Exception $e)
    {
        $this->writeln($this->verbose ? $e : $e->getMessage(), 'error', $conn);
        //$this->writeln(print_r($e, true));
        $conn->close();
        $this->clients->detach($conn);
    }
}
