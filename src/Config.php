<?php

namespace App;

class Config
{
    public const LiveTradesUrl = 'wss://api.bitfinex.com/ws/1';
    public const LiveTradesSubscribe = [
        '{"event": "subscribe", "channel": "trades", "pair": "BTCUSD"}',
        '{"event": "subscribe", "channel": "trades", "pair": "BTCEUR"}',
    ];
    public const LiveTradesSymbol = ['BTCUSD', 'BTCEUR'];
    public const LiveTradesTransport = 'liveTrades'; // Sync: messenger.yaml, Config::LiveTradesTransport
}
