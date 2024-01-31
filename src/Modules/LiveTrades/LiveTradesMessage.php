<?php

namespace App\Modules\LiveTrades;

final readonly class LiveTradesMessage
{
    /** @param $trades object[][] */
    public function __construct(
        private array $trades, // [ 'BTCUSD' => [ '1494734166-tBTCUSD', 1705081553, 43535, 0.0156206 ] ]
    )
    {}

    /** @return object[][] */
    public function getTrades(): array
    {
        return $this->trades;
    }
}
