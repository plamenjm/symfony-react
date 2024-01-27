<?php

namespace App\Controller;

use App\Service\LiveTradesEvents;
use Closure;
use Ratchet\ConnectionInterface;

final class LiveTradesControllerLog extends LiveTradesControllerBase
{
    private LiveTradesEvents $events;

    /** @param $writelnCb Closure(string $message): void
     * @noinspection PhpDocSignatureIsNotCompleteInspection */
    public function init(bool $verbose, Closure $writelnCb, LiveTradesEvents $events = null)
    {
        parent::init($verbose, $writelnCb);
        $this->events = $events;
    }


    //---

    function onMessage(ConnectionInterface $from, $msg)
    {
        //$path = $this->requestPath($from);
        //$this->writeln('PATH: ' . $path);
        //if ($path === 'events') {
        //    $query = $this->requestQuery($from);
        //    $this->writeln('QUERY: ' . $query['symbol'] . ' ' . $query['from'] . ' ' . $query['to']);
        //}

        $json = json_decode($msg);
        if ($json->event === 'log' && $json->channel === 'trades') { // {"event": "log", "channel": "trades", "pair": "BTCUSD", "from": 1705081553, "to": 1705081553}
            if (isset($this->events->trades[$json->pair])) {
                $eventsAll = $this->events->trades[$json->pair]; // [ 'BTCUSD' => [ '1494734166-tBTCUSD', 1705081553, 43535, 0.0156206 ] ]
                $events = array_filter($eventsAll, fn($event) => $json->from <= $event[1] && $event[1] < $json->to);
                if (count($events)) {
                    //$clients = new SplObjectStorage();
                    //$clients->attach($from);
                    //$this->messageSend(json_encode([0, array_values($events)]), count($events), $clients);
                    $this->writeln(count($events) . ' messages', '<', $from);
                    $from->send(json_encode([0, array_values($events)]));
                    return;
                }
            }
        }

        $this->writeln($msg, '>', $from);
        if ($this->verbose) $this->writeln($msg, '<', $from);
        $from->send($msg); // echo
    }
}
