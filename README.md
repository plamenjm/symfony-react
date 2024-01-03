# symfony-react

Playground (Symfony, SQLite, ReactJS)

![image](https://github.com/plamenjm/symfony-react/assets/56994434/62e8530d-bc4a-4b7b-b10e-57dca5b887d7)

![image](https://github.com/plamenjm/symfony-react/assets/56994434/61e046e9-557a-40a5-8426-9a6322100cfd)

![image](https://github.com/plamenjm/symfony-react/assets/56994434/b5f25e40-dd6f-45ca-bbc4-9b2c8c766c72)

---

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

todo: symfony web security
todo: symfony db users, authentication
todo: react state (redux, mobx)
todo: bootstrap, sass, styled-components
```

---

cmd.sh

```
Helper script for symfony and podman (docker) container.

Usage: cmd.sh <serve | serve-debug | stop | log-php | dump>
       cmd.sh <watch | dev-server | dev-live-php | dev-live>
       cmd.sh <phpunit $* | phpunit-dump $* | lint>
       cmd.sh <log-dev | browser | bash $* >
```

---

prod hints

```
$ bin/console lint:container
```
