<?php

namespace App\EventListener;

use Symfony\Contracts\EventDispatcher\Event;

final class LiveEventsMessageEvent extends Event
{
    /** @param $trades object[][]
     * @noinspection PhpDocSignatureIsNotCompleteInspection */
    public function __construct(
        private readonly string $message,
        private readonly array $trades, // [ 'BTCUSD' => [ '1494734166-tBTCUSD', 1705081553, 43535, 0.0156206 ] ]
    )
    {}

    public function getMessage(): string
    {
        return $this->message;
    }

    /** @return object[][] */
    public function getTrades(): array
    {
        return $this->trades;
    }
}
