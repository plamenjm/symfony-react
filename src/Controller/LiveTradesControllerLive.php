<?php

namespace App\Controller;

use App\EventListener\LiveEventsMessageEvent;
use Ratchet\ConnectionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LiveTradesControllerLive extends LiveTradesControllerBase implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            LiveEventsMessageEvent::class => ['onMessageEvent'],
        ];
    }


    //---

    public function onMessageEvent(LiveEventsMessageEvent $event)
    {
        $trades = $event->getTrades(); // [ 'BTCUSD' => [ '1494734166-tBTCUSD', 1705081553, 43535, 0.0156206 ] ]
        foreach($trades as $symbol => $events)
            $this->messageSend(json_encode([0, $events]), count($events), $this->subscribed[$symbol]);
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
