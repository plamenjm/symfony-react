//


//---

export const setConfig = (config: object) => Config = Object.freeze({...Config, ...config}) // appJSConfig from PHP controller

export let Config = Object.freeze({
    DevLogEnable: false,
    DevFakerEnable: true,

    //---

    FetchApi: '', //'/api/', // appJSConfig from PHP controller. See: Config.php, WebController.php


    //---

    LiveTradesUrl: 'ws://localhost:8002',
    //LiveTradesSubscribe: [
    //    '{"event": "subscribe", "channel": "trades", "pair": "BTCUSD"}',
    //    '{"event": "subscribe", "channel": "trades", "pair": "BTCEUR"}',
    //],

    //LiveTradesUrl: '', //'wss://api.bitfinex.com/ws/1', // appJSConfig from PHP controller. See: Config.php, WebController.php
    LiveTradesSubscribe: [''], // appJSConfig from PHP controller. See: Config.php, WebController.php

    LiveTradesAutoConnect: false,
    LiveTradesKeepMessages: 10,
    LiveTradesAggregateEvents: true,
})


//---

type TSDevFaker = {
    number: {int: (arg: object) => number},
    date: {between: (arg: object) => Date},
}

export let DevFaker: undefined | Readonly<TSDevFaker> //Readonly<Faker> //import {Faker} from '@faker-js/faker';

if (Config.DevFakerEnable) import('@faker-js/faker').then(res => DevFaker = Object.freeze(res.faker as TSDevFaker))
