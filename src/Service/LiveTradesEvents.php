<?php

namespace App\Service;

use App\Config;
use App\EventListener\LiveEventsMessageEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LiveTradesEvents implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            LiveEventsMessageEvent::class => ['onMessageEvent'],
        ];
    }


    //---

    /** @param $trades object[][] */
    public function __construct(
        public array $trades = [], // [ 'BTCUSD' => [ '1494734166-tBTCUSD', 1705081553, 43535, 0.0156206 ] ]
    )
    {
        foreach (Config::LiveTradesSymbol as $symbol) $this->trades[$symbol] = [];
    }

    public function onMessageEvent(LiveEventsMessageEvent $event)
    {
        $trades = $event->getTrades();
        foreach($trades as $symbol => $events)
            array_push($this->trades[$symbol], ...$events);
    }
}
