<?php

namespace App\Controller;

use App\Classes\LiveTradesControllerBase;
use App\Service\LiveTradesStore;
use Ratchet\ConnectionInterface;

final class LiveTradesControllerLog extends LiveTradesControllerBase
{
    public function __construct(
        private readonly LiveTradesStore $liveTradesStore,
    )
    {
        parent::__construct();
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
            if (isset($this->liveTradesStore->trades[$json->pair])) {
                $eventsAll = $this->liveTradesStore->trades[$json->pair]; // [ 'BTCUSD' => [ '1494734166-tBTCUSD', 1705081553, 43535, 0.0156206 ] ]
                $events = array_filter($eventsAll, fn($event) => $json->from <= $event[1] && $event[1] < $json->to);
                if (count($events)) {
                    $log = count($events) . ' messages (' . $json->pair . ', ' . gmdate('Y-m-d H:i:s', $json->from) . '/' . gmdate('Y-m-d H:i:s', $json->to) . ')';
                    $this->writeln($log, '<', $from);
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
