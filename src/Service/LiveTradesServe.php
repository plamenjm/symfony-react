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
    private ?int $port = null;
    private ?string $address = null;
    private ?IoServer $server;

    /** @var ?Closure(string $message): void */
    private ?Closure $writelnCb = null;

    private $withRouter = true;

    public function __construct(
        private readonly LiveTradesControllerLive $compLive,
        private readonly LiveTradesControllerLog $compLog,
        private readonly LiveTradesEvents $events,
        private readonly ParameterBagInterface $params,
    )
    {}

    /** @param $writeln ?Closure(string $message): void
     * @noinspection PhpDocSignatureIsNotCompleteInspection */
    public function init(int $port = 0, string $address = '',
                            $verbose = false, ?Closure $writeln = null): void
    {
        $this->port = $port ?: $this->params->get('liveTradesPort');
        $this->address = $address ?: $this->params->get('liveTradesListen');
        $this->writelnCb = $writeln;

        if (!$this->withRouter) {
            $this->compLive->init($verbose, $this->writelnCb);
            $this->ioServer(new WsServer($this->compLive));
            return;
        }

        // See: github.com/ratchetphp/Ratchet
        // $app = new \Ratchet\App();
        // $app->route($path, $ws, $allowedOrigins, $httpHost);
        // $app->run();
        $routes = new RouteCollection();
        $matcher = new UrlMatcher($routes, new RequestContext());
        $this->ioServer(new Router($matcher));

        $httpHost = ''; //'localhost';
        $requirements = ['Origin' => $httpHost ?: '*'];

        $this->compLive->init($verbose, $this->writelnCb);
        $ws = new WsServer($this->compLive);
        //$ws->enableKeepAlive($this->server->loop);
        $path = '/live';
        $routes->add($path, new Route($path, ['_controller' => $ws], $requirements, [], $httpHost, [], ['GET']));

        $this->compLog->init($verbose, $this->writelnCb, $this->events);
        $ws = new WsServer($this->compLog);
        //$ws->enableKeepAlive($this->server->loop);
        $path = '/log';
        $routes->add($path, new Route($path, ['_controller' => $ws], $requirements, [], $httpHost, [], ['GET']));
    }

    public function run(): void
    {
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
