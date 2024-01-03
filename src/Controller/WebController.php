<?php // $ bin/console make:controller WebController

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WebController extends BaseController //implements \Symfony\Contracts\Service\ServiceSubscriberInterface
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            //\Psr\Log\LoggerInterface::class, // optional: '?' . ...
        ]);
    }


    //---

    /** @noinspection PhpInapplicableAttributeTargetDeclarationInspection */
    public function __construct(
        #[\Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure(\Psr\Log\LoggerInterface::class)]
        private readonly \Closure $logger,

        //#[\Symfony\Component\DependencyInjection\Attribute\AutowireLocator([\Psr\Log\LoggerInterface::class])] //? not subscribed, not working
        //private readonly \Psr\Container\ContainerInterface $subscribed,

        //\Twig\Environment $twig,
    )
    {
        parent::__construct(); //$twig
    }

    private function getLogger(): \Psr\Log\LoggerInterface
    {
        return ($this->logger)(); // lazy by closure (getter)
    }

    //private function locateLogger(): \Psr\Log\LoggerInterface
    //{
    //    //if (!$this->subscribed->has(\Psr\Log\LoggerInterface::class)) ... // optional
    //    /** @noinspection PhpUnhandledExceptionInspection */
    //    return $this->subscribed->get(\Psr\Log\LoggerInterface::class); // lazy by subscribe (locator)
    //}


    //---

    //#[Route('/', name: 'homepage')] // to-do
    #[Route('/index', name: '/index')]
    public function index(
        //#[\Symfony\Component\DependencyInjection\Attribute\Autowire(service: \Psr\Log\LoggerInterface::class, lazy: true)]
        //\Psr\Log\LoggerInterface $logger, // lazy by proxy
    ): Response
    {
        if ($_SERVER['APP_ENV'] !== 'test') { // testDump
            //\App\TestDump::dd(['index']);
            //\App\TestDump::exception(['index']);
            //\App\TestDump::varDump(['index']);
            \App\TestDump::stdErr(['index']);
            //\App\TestDump::dump(['index']);
            \App\TestDump::logger(['index'], $this->getLogger()); //$this->locateLogger() //$logger
            \App\TestDump::console(['index']);
        }


        return $this->render('index.html.twig');
    }

    #[Route('/db', name: '/db')]
    public function db(): Response
    {
        return $this->render('db.html.twig');
    }

    #[Route('/spa/{page}', name: '/spa', defaults: ['page' => ''])]
    public function spa(Request $request): Response
    {
        $route = $request->attributes->get('_route');
        $url = $this->generateUrl($route, [], UrlGeneratorInterface::ABSOLUTE_URL);
        $urlApi = implode('/', array_slice(explode('/', $url), 0, 3))
            . \App\Constants::APP_PATH_API;

        return $this->render('spa.html.twig', [
            //'route' => $route, //'path' => $this->generateUrl($routeName),
            'urlApi' => $urlApi,
        ]);
    }
}
