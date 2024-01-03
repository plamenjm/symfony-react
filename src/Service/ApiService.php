<?php

namespace App\Service;

class ApiService implements \Symfony\Contracts\Service\ServiceSubscriberInterface
{
    use \Symfony\Contracts\Service\ServiceSubscriberTrait;

    private array $testHappyMessages = ['Amazing!', 'You did it!', 'Great work!', 'Keep going!'];


    //---



    //---

    //public static function getSubscribedServices(): array
    //{
    //    return [
    //        //\Psr\Log\LoggerInterface::class, // optional: '?' . ...
    //    ];
    //}


    //---

    ///** @noinspection PhpInapplicableAttributeTargetDeclarationInspection */
    public function __construct(
        //#[\Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure(\Psr\Log\LoggerInterface::class)]
        //private readonly \Closure $logger,

        //#[\Symfony\Component\DependencyInjection\Attribute\AutowireLocator([\Psr\Log\LoggerInterface::class])] //? not subscribed, not working
        //private readonly \Psr\Container\ContainerInterface $subscribed,

        //#[\Symfony\Component\DependencyInjection\Attribute\Autowire(env: 'APP_ENV')]
        //private readonly string $APP_ENV,
    )
    {}

    //private function getLogger(): \Psr\Log\LoggerInterface
    //{
    //    return ($this->logger)(); // lazy by closure (getter)
    //}

    //private function locateLogger(): \Psr\Log\LoggerInterface
    //{
    //    //if (!$this->subscribed->has(\Psr\Log\LoggerInterface::class)) ... // optional
    //    /** @noinspection PhpUnhandledExceptionInspection */
    //    return $this->subscribed->get(\Psr\Log\LoggerInterface::class); // lazy by subscribe (locator)
    //}

    #[\Symfony\Contracts\Service\Attribute\SubscribedService]
    private function logger(): ?\Psr\Log\LoggerInterface
    {
        return $this->container?->get(__METHOD__); // lazy by subscribe (locator)
    }


    //---

    public function testHappyMessage(
        //#[\Symfony\Component\DependencyInjection\Attribute\Autowire(service: \Psr\Log\LoggerInterface::class, lazy: true)]
        //\Psr\Log\LoggerInterface $logger, // lazy by proxy
    ): string
    {
        if ($_SERVER['APP_ENV'] !== 'test') { // testDump
            //\App\TestDump::dd('message');
            //\App\TestDump::exception('message');
            //\App\TestDump::varDump('message');
            \App\TestDump::stdErr('message');
            \App\TestDump::dump('message');
            \App\TestDump::logger('message', $this->logger()); //$this->getLogger() //$this->locateLogger() //$logger
        }


        return $this->testHappyMessages[array_rand($this->testHappyMessages)];
    }
}
