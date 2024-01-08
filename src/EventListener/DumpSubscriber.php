<?php // $ bin/console make:subscriber DumpSubscriber kernel.response

namespace App\EventListener;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelInterface;

final class DumpSubscriber implements EventSubscriberInterface
{
    private const dumpEnableStdErrAndCollect = false; // dump to StdErr, JS console.log (collect) // replaced with DumpClonerDecorator
    private const dumpCollectEnable = true; // JS console.log (collect)


    //---

    public static function getSubscribedEvents(): array
    {
        $res = [];
        if (class_exists(ConsoleEvents::class)) $res += [
            ConsoleEvents::COMMAND => ['onConsoleCommand_first', 1023], //ConsoleCommandEvent::class
        ];
        $res += [
            ResponseEvent::class => [ //\Symfony\Component\HttpKernel\KernelEvents::RESPONSE
                ['onKernelResponse_last', -129],
                ['onKernelResponse'],
            ],
        ];
        return $res;
    }

    private \Closure $dumpHandlerPrev;
    private static array $DUMP_COLLECT = []; // to-do: use EventDispatcher? use DumpDataCollector?

    public static function dumpCollect($var): void
    {
        if (!self::dumpCollectEnable) return;
        //$size = sizeof(self::$DUMP_COLLECT);
        //if ($size && self::$DUMP_COLLECT[$size - 1] === $var) return; // duplicate
        self::$DUMP_COLLECT[] = $var;
    }


    //---

    public function __construct(
        private readonly KernelInterface $kernel,
    )
    {}

    //#[\Symfony\Component\EventDispatcher\Attribute\AsEventListener(event: ConsoleCommandEvent::class, priority: 1023)]
    public function onConsoleCommand_first(ConsoleCommandEvent $event): void
    {
        if ($this->kernel->isDebug()) {
            // after DumpListener // 1024 = \Symfony\Component\HttpKernel\EventListener\DumpListener::getSubscribedEvents()
            if (self::dumpEnableStdErrAndCollect) $this->dumpHandlerPrev = \Symfony\Component\VarDumper\VarDumper::setHandler(function ($var, string $label = null) {
                \App\Utils::stdErr($var); // to-do: if dump_destination is not php://stderr

                self::dumpCollect($var);

                ($this->dumpHandlerPrev)($var, $label);
            });
        }
    }

    //#[\Symfony\Component\EventDispatcher\Attribute\AsEventListener(event: RequestEvent::class)]
    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->kernel->isDebug()) {
            if ($event->isMainRequest()) {
                $request = $event->getRequest();
                if (!$request->isXmlHttpRequest()
                    //&& $request->query->has('profilerReplace')
                    && str_starts_with($request->attributes->get('_route'), \App\Constants::APP_ROUTE_API))
                    $event->getResponse()->headers->set('Symfony-Debug-Toolbar-Replace', '1');
            }
        }
    }

    //#[\Symfony\Component\EventDispatcher\Attribute\AsEventListener(event: RequestEvent::class, priority: -129)]
    public function onKernelResponse_last(ResponseEvent $event): void
    {
        if ($this->kernel->isDebug()) {
            // after WebDebugToolbarListener (headers Content-Type, X-Debug-Token-Link) // -128 = \Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener::getSubscribedEvents()
            if (sizeof(self::$DUMP_COLLECT)) {
                $response = $event->getResponse();
                if ($response->headers->has('X-Debug-Token-Link') // optional
                    && $response->headers->has('Content-Type')
                    && str_contains($response->headers->get('Content-Type') ?? '', 'html')) {
                    $res = '';
                    for ($i = 0; $i < sizeof(self::$DUMP_COLLECT); $i++)
                        $res .= \App\Utils::JSConsole(self::$DUMP_COLLECT[$i], false);
                    $response->setContent($response->getContent() . $res);
                }
            }
        }
    }
}
