# symfony-react

Playground - Symfony, SQLite, phpunit; WebSocket; React.js, Chart.js, TypeScript


<details><summary>

### Live Trades frontend - SPA React.js, WebSocket client, live Chart.js

</summary>

get events by date/time, subscribe for real-time events (live), back/forward, hour/day/week, aggregation

![image](https://github.com/plamenjm/symfony-react/assets/56994434/a3d09dc2-0b29-44c9-bbba-69eeaac753c9)

![image](https://github.com/plamenjm/symfony-react/assets/56994434/c0ac58c4-bcda-4a0c-abd1-08f0fe927a52)

![image](https://github.com/plamenjm/symfony-react/assets/56994434/48977f05-04c4-4526-b3a8-47174bfc4a25)

![image](https://github.com/plamenjm/symfony-react/assets/56994434/378d8d29-b4ca-4d23-ac84-e683c46328ad)

</details>

<details><summary>

### Live Trades backend - PHP/Ratchet WebSocket client and server

</summary>

```
$ bin/console liveTrades:client
$ bin/console liveTrades:serve
```

![image](https://github.com/plamenjm/symfony-react/assets/56994434/718e3445-ce5a-4644-b7d8-aade9264b318)

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
$ symfony new symfony-react
$ cd symfony-react

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

todo: websocket router
todo: symfony web security
todo: symfony db users, authentication
todo: react state (redux, mobx)
todo: bootstrap, sass, styled-components
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
