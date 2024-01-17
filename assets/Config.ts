//


//---

export const Config = Object.freeze({
    DevLogEnable: false,
    DevFakerEnable: true,

    //---

    FetchApi: Object.seal({value: '/api/'}),


    //---

    //LiveTradesUrl: 'ws://localhost:8002'
    LiveTradesUrl: 'wss://api.bitfinex.com/ws/1',
    LiveTradesSymbol: {USD: 'tBTCUSD', EUR: 'tBTCEUR'},
    LiveTradesSubscribe: [
        '{"event": "subscribe", "channel": "trades", "pair": "BTCUSD"}',
        '{"event": "subscribe", "channel": "trades", "pair": "BTCEUR"}',
    ],
    LiveTradesAutoConnect: false,
    LiveTradesMaxMessages: 10,
    LiveTradesAggregateEvents: true,
})


//---

type TSDevFaker = {
    number: {int: (arg: object) => number},
    date: {between: (arg: object) => Date},
}

export let DevFaker: undefined | Readonly<TSDevFaker> //Readonly<Faker> //import {Faker} from '@faker-js/faker';

if (Config.DevFakerEnable) import('@faker-js/faker').then(res => DevFaker = Object.freeze(res.faker as TSDevFaker))
