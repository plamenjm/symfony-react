<?php

namespace App\Controller;

use App\Classes\LiveTradesControllerBase;
use App\Config;
use App\Modules\LiveTrades\LiveTradesEvent;
use Ratchet\ConnectionInterface;
use SplObjectStorage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LiveTradesControllerLive extends LiveTradesControllerBase implements EventSubscriberInterface
{
    /** @var SplObjectStorage[] */
    private array $subscribed = [];


    //---

    public static function getSubscribedEvents(): array
    {
        return [
            LiveTradesEvent::class => ['eventHandler'],
        ];
    }


    //---

    public function __construct()
    {
        parent::__construct();
        foreach (Config::LiveTradesSymbol as $symbol) $this->subscribed[$symbol] = new SplObjectStorage;
    }

    public function eventHandler(LiveTradesEvent $event)
    {
        $trades = $event->getTrades();
        foreach($trades as $symbol => $events) { // [ 'BTCUSD' => [ '1494734166-tBTCUSD', 1705081553, 43535, 0.0156206 ] ]
            if (!$this->subscribed[$symbol]->count()) break;
            $message = json_encode([0, $events]);
            $count = count($events);
            foreach ($this->subscribed[$symbol] as $client) {
                if ($this->verbose || $count > 1)
                    $this->writeln($count === 1 ? $message : $count . ' messages', '<', $client);
                $client->send($message);
            }
        }
    }

    function onMessage(ConnectionInterface $from, $msg)
    {
        $json = json_decode($msg);
        if ($json && $json->event === 'subscribe' && $json->channel === 'trades') { // {"event": "subscribe", "channel": "trades", "pair": "BTCUSD"}
            if (isset($this->subscribed[$json->pair])) $this->subscribed[$json->pair]->attach($from);
        }

        $this->writeln($msg, '>', $from);
        if ($this->verbose) $this->writeln($msg, '<', $from);
        $from->send($msg); // echo
    }
}
