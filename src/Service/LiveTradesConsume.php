<?php

namespace App\Service;

use App\Config;
use App\Modules\LiveTrades\LiveTradesConsumeWorker;
use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\AckStamp;
use Symfony\Component\Messenger\Stamp\ConsumedByWorkerStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Throwable;

final class LiveTradesConsume implements EventSubscriberInterface
{
    private bool $useWorker = true;
    private LoopInterface $loop;


    //---

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerRunningEvent::class => ['onWorkerRunningEvent'],
        ];
    }


    //---

    /** @param $writelnCb ?Closure(string $message): void
     * @noinspection PhpDocSignatureIsNotCompleteInspection */
    public function __construct(
        /** @noinspection PhpInapplicableAttributeTargetDeclarationInspection */
        #[Autowire(service: 'messenger.receiver_locator')]
        private readonly ContainerInterface $receiverLocator,
        ///** @noinspection PhpInapplicableAttributeTargetDeclarationInspection */
        //#[Autowire(service: 'messenger.routable_message_bus')]
        //private readonly \Symfony\Component\Messenger\RoutableMessageBus $messageBus,
        private readonly MessageBusInterface $messageBus,
        private readonly EventDispatcherInterface $eventDispatcher,
        private ?Closure $writelnCb = null,
    )
    {
        $this->loop = Loop::get();
    }

    /** @param $writeln ?Closure(string $message): void */
    public function init(
        ?Closure $writeln = null,
    ): void
    {
        $this->writelnCb = $writeln;
    }

    public function run(): bool
    {
        //try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $receiver = $this->receiverLocator->get(Config::LiveTradesTransport);
        //} catch (Throwable $ex) {
        //    $this->writeln($ex->getMessage());
        //    return false;
        //}

        $onWork = function() use ($receiver) { // See: vendor/symfony/messenger/Command/ConsumeMessagesCommand.php
            if ($this->useWorker) {
                $worker = new LiveTradesConsumeWorker([Config::LiveTradesTransport => $receiver],
                    $this->messageBus, $this->eventDispatcher);
                $worker->run(['sleep' => 0]);
            } else {
                $envelopes = $receiver->get();
                foreach ($envelopes as $envelope) {
                    $this->messageBus->dispatch($envelope->with(new ReceivedStamp(Config::LiveTradesTransport), new ConsumedByWorkerStamp(),
                        new AckStamp(fn(Envelope $envelope, Throwable $ex = null) => !$ex ?: $receiver->reject($envelope))));
                    $receiver->ack($envelope);
                }
            }
        };

        $this->writeln('[Consuming messages from transport "' . Config::LiveTradesTransport . '"]');
        //try {
            $onWork();
        //} catch (Throwable $ex) {
        //    $this->writeln($ex->getMessage());
        //    return false;
        //}
        $this->loop->addPeriodicTimer(1, $onWork);
        return true;
    }


    //---

    public function onWorkerRunningEvent(WorkerRunningEvent $event): void
    {
        if ($event->getWorker() instanceof LiveTradesConsumeWorker && $event->isWorkerIdle())
            $event->getWorker()->stop();
    }

    private function writeln(string $message = ''): void
    {
        if ($this->writelnCb) ($this->writelnCb)($message);
    }
}
