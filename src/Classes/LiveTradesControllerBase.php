<?php

namespace App\Classes;

use Closure;
use Exception;
//use GuzzleHttp\Psr7\Request;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

//#[\Symfony\Component\DependencyInjection\Attribute\Exclude]
abstract class LiveTradesControllerBase implements MessageComponentInterface
{
    private SplObjectStorage $clients;
    protected string $route = '';
    protected bool $verbose = false;

    /** @var ?Closure(string $message): void */
    private ?Closure $writelnCb = null;

    public function __construct()
    {
        $this->clients = new SplObjectStorage;
    }

    /** @param $writelnCb ?Closure(string $message): void
     * @noinspection PhpDocSignatureIsNotCompleteInspection */
    public function init(
        string $route = '',
        bool $verbose = false,
        ?Closure $writelnCb = null,
    )
    {
        $this->route = $route;
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
        else $pref = '[' . $this->route . ($this->resourceId($conn) ?: '') . $addr . '/' . count($this->clients) . $sub . ']';

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
