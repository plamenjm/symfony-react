<?php

namespace App\Service;

use App\Controller\LiveTradesControllerLive;
use App\Controller\LiveTradesControllerLog;
use Closure;
use Ratchet\Http\HttpServer;
use Ratchet\Http\Router;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class LiveTradesServe
{
    private bool $routeLive = true;
    private bool $routeLog = true;
    private string $httpHost = 'localhost'; //''

    private UrlMatcher $urlMatcher;
    private IoServer $server;

    /** @param $writelnCb ?Closure(string $message): void
     * @noinspection PhpDocSignatureIsNotCompleteInspection */
    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly LiveTradesControllerLive $controllerLive,
        private readonly LiveTradesControllerLog $controllerLog,
        LiveTradesStore $liveTradesStore,
        private ?int $port = null,
        private ?string $address = null,
        private ?Closure $writelnCb = null,
    )
    {
        $liveTradesStore->init(true);
    }

    /** @param $writeln ?Closure(string $message): void
     * @noinspection PhpDocSignatureIsNotCompleteInspection */
    public function init(
        int $port = 0,
        string $address = '',
        $verbose = false,
        ?Closure $writeln = null,
    ): void
    {
        $this->port = $port ?: $this->params->get('liveTradesPort');
        $this->address = $address ?: $this->params->get('liveTradesListen');
        $this->writelnCb = $writeln;

        $this->controllerLive->init('live/', $verbose, $this->writelnCb);
        $this->controllerLog->init('log/', $verbose, $this->writelnCb);

        if ($this->routeLive && $this->routeLog) {
            // See: github.com/ratchetphp/Ratchet
            // $app = new \Ratchet\App();
            // $app->route($path, $ws, $allowedOrigins, $this->httpHost);
            // $app->run();
            $requirements = ['Origin' => $this->httpHost ?: '*'];
            $routes = new RouteCollection();

            $ws = new WsServer($this->controllerLive);
            //$ws->enableKeepAlive($this->server->loop);
            $path = '/live';
            $routes->add($path, new Route($path, ['_controller' => $ws], $requirements, [], $this->httpHost, [], ['GET']));

            $ws = new WsServer($this->controllerLog);
            //$ws->enableKeepAlive($this->server->loop);
            $path = '/log';
            $routes->add($path, new Route($path, ['_controller' => $ws], $requirements, [], $this->httpHost, [], ['GET']));

            $this->urlMatcher = new UrlMatcher($routes, new RequestContext());
        }
    }

    public function run(): void
    {
        if ($this->routeLive && $this->routeLog) {
            $this->ioServer(new Router($this->urlMatcher));
        } else if (!$this->routeLive) {
            $this->ioServer(new WsServer($this->controllerLog));
        } else if (!$this->routeLog) {
            $this->ioServer(new WsServer($this->controllerLive));
        }
        $this->writeln('[ws://' . $this->address . ':' . $this->port . ' listening]');
        $this->server->run();
    }


    //---

    private function ioServer($http): void
    {
        $app = new HttpServer($http);
        //$this->server = IoServer::factory($app, $this->port, $this->address);
        $loop = Loop::get();
        $socket = new SocketServer($this->address . ':' . $this->port, loop: $loop);
        $this->server = new IoServer($app, $socket, $loop);
    }

    private function writeln(string $message = ''): void
    {
        if ($this->writelnCb) ($this->writelnCb)($message);
    }
}
