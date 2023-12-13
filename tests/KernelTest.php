<?php

namespace App\Tests;

//use Psr\Log\LoggerInterface;
use App\Service\Utils;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
//use Symfony\Component\Routing\RouterInterface;

class KernelTest extends KernelTestCase
{
    public function test(): void
    {
        $kernel = self::bootKernel();


        // debug log and dump
        //? $logger = (fn($v): LoggerInterface => $v)(static::getContainer()->get('logger'));
        //? $logger->debug('test', ['KernelTest']);


        $this->assertSame('test', $kernel->getEnvironment());

        ///** @var Utils $utils */ $utils = static::getContainer()->get('utils');
        //$this->assertNotEmpty($utils->happyMessage());

        /** @var Utils $utils */ $utils = static::getContainer()->get(Utils::class);
        $this->assertNotEmpty($utils->happyMessage());
    }
}
