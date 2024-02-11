<?php

namespace App\Service;

use App\Config;
use App\Modules\LiveTrades\LiveTradesEvent;
use App\Modules\LiveTrades\LiveTradesMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

final class LiveTradesStore implements EventSubscriberInterface
{
    public array $trades = []; // [ 'BTCUSD' => [ '1494734166-tBTCUSD', 1705081553, 43535, 0.0156206 ] ]


    //---

    public static function getSubscribedEvents(): array
    {
        return [
            LiveTradesEvent::class => ['eventHandler'],
        ];
    }


    //---

    /** @param $trades object[][] */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private bool $withMessenger = false,
    )
    {
        foreach (Config::LiveTradesSymbol as $symbol) $this->trades[$symbol] = [];
    }

    public function init(
        bool $withMessenger,
    ): void
    {
        $this->withMessenger = $withMessenger;
    }

    #[AsMessageHandler(fromTransport: Config::LiveTradesTransport)]
    public function messageHandler(LiveTradesMessage $message)
    {
        if ($this->withMessenger)
            $this->eventDispatcher->dispatch(new LiveTradesEvent($message->getTrades()));
    }

    public function eventHandler(LiveTradesEvent $event)
    {
        $trades = $event->getTrades();
        foreach($trades as $symbol => $events) { // [ 'BTCUSD' => [ '1494734166-tBTCUSD', 1705081553, 43535, 0.0156206 ] ]
            if (count($events)) array_push($this->trades[$symbol], ...$events);
        }
    }
}
