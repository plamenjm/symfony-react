# symfony-react

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
```

```
$ bin/console lint:container
$ symfony serve -d
$ symfony local:server:stop

$ npm run watch; # watch and compile assets 
$ npm run dev-server --live-reload; # development server
```
