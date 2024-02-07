# symfony-react

Playground (Symfony 7, SQLite, phpunit; WebSocket, RabbitMQ; React.js 18, Chart.js, TypeScript)


<details><summary>

### Live Trades - Ratchet WebSocket, RabbitMQ AMQP, React.js, Chart.js (live)

</summary>

Backend: WebSocket client and server with RabbitMQ AMQP message broker.
Frontend: Subscribe for real-time events (live). Get events log. Aggregate data. Display hour/day/week view.

```
$ bin/console liveTrades:client
2024-01-31 08:08:41 [wss://api.bitfinex.com/ws/1 open]
2024-01-31 08:08:42 [wss://api.bitfinex.com/ws/1 <] {"event": "subscribe", "channel": "trades", "pair": "BTCUSD"}
2024-01-31 08:08:42 [wss://api.bitfinex.com/ws/1 <] {"event": "subscribe", "channel": "trades", "pair": "BTCEUR"}
...................................................................................................
2024-01-31 08:14:07 [messages] 100 (saved: 86), memory: 5 MB
2024-01-31 09:03:23 [messages] 1000 (saved: 339), memory: 8 MB 
...
```

```
$ bin/console liveTrades:serve
2024-01-31 08:31:33 [Consuming messages from transport "liveTrades"]
2024-01-31 08:31:33 [ws://10.0.2.100:8002 listening]
2024-01-31 08:31:34 [live/887/1 open]
2024-01-31 08:31:34 [live/887/1 >] {"event": "subscribe", "channel": "trades", "pair": "BTCUSD"}
2024-01-31 08:31:34 [live/887/1 >] {"event": "subscribe", "channel": "trades", "pair": "BTCEUR"}
2024-01-31 08:32:11 [log/1029/1 open]
2024-01-31 08:32:11 [log/1029/1 <] 21 messages (BTCUSD, 2024-01-30 09:00:00/2024-01-31 09:00:01)
2024-01-31 08:32:11 [log/1029/0 close]
2024-01-31 08:32:18 [log/1038/1 open]
2024-01-31 08:32:18 [log/1038/1 <] 12 messages (BTCEUR, 2024-01-31 07:35:00/2024-01-31 08:35:01)
2024-01-31 08:32:18 [log/1038/0 close]
...
2024-01-31 08:54:37 [live/887/0 close]
```

![image](https://github.com/plamenjm/symfony-react/assets/56994434/fb95b27f-be42-422d-8df4-554dbe5ea248)

![image](https://github.com/plamenjm/symfony-react/assets/56994434/a3d09dc2-0b29-44c9-bbba-69eeaac753c9)

![image](https://github.com/plamenjm/symfony-react/assets/56994434/c0ac58c4-bcda-4a0c-abd1-08f0fe927a52)

![image](https://github.com/plamenjm/symfony-react/assets/56994434/48977f05-04c4-4526-b3a8-47174bfc4a25)

![image](https://github.com/plamenjm/symfony-react/assets/56994434/378d8d29-b4ca-4d23-ac84-e683c46328ad)

</details>


<details><summary>

### `dump()` to JS console and to StdErr

</summary>

![image](https://github.com/plamenjm/symfony-react/assets/56994434/ce4e20bd-942b-4926-b913-79fd3aac66f9)

</details>


<details><summary>

### phpunit

</summary>

![image](https://github.com/plamenjm/symfony-react/assets/56994434/b5f25e40-dd6f-45ca-bbc4-9b2c8c766c72)

</details>


<details><summary>

#### done installs and todo notes

</summary>

```
$ symfony new symfony-react; cd symfony-react
$ composer require webapp; # symfony new symfony-react --webapp 

$ composer require symfony/webpack-encore-bundle; # ux-react
$ npm install
$ composer require symfony/stimulus-bundle; # ux-react
$ npm install
$ composer require symfony/ux-react
$ npm install
$ npm install --save-dev @babel/preset-react; # ux-react
$ npm run dev; # compile assets

$ #composer require --dev symfony/maker-bundle
$ bin/console make:controller ReactController
$ composer require api; # not needed yet
$ composer require --dev symfony/test-pack
$ bin/phpunit

$ composer require symfony/process
$ npm install --save-dev typescript ts-loader fork-ts-checker-webpack-plugin; # PhpStorm settings TypeScript: Bundled
$ npm install --save-dev eslint @typescript-eslint/parser @typescript-eslint/eslint-plugin; # PhpStorm settings ESLint: automatic
$ npm install react-router-dom

$ composer require symfony/orm-pack
$ bin/console doctrine:database:create
$ bin/console make:entity ...
$ bin/console doctrine:migrations:diff; # bin/console make:migration
$ bin/console doctrine:migrations:migrate
$ bin/console dbal:run-sql 'SELECT * FROM ...'

$ npm install --save-dev wscat
$ npm install react-use-websocket
$ npm install react-chartjs-2
$ npm install --save-dev @faker-js/faker
$ composer require ratchet/pawl
$ composer require cboden/ratchet; # from RatchetSymfony7
$ composer require symfony/event-dispatcher
$ composer require symfony/messenger
$ composer require symfony/amqp-messenger
```

</details>


<details><summary>

##### cmd.sh

</summary>

```
Helper script for symfony and podman (docker) container.

Usage: cmd.sh <serve | serve-debug | stop | log-php | dump>
       cmd.sh <watch | dev-server | dev-live-php | dev-live>
       cmd.sh <phpunit $* | phpunit-dump $* | lint>
       cmd.sh <rabbitmq | liveTrades-serve | liveTrades-client>
       cmd.sh <log-dev | browser | bash $* >
```

</details>


<details><summary>

###### notes/hints

</summary>

```
$ bin/console lint:container
```

</details>
