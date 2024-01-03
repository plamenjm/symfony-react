<?php

namespace App\Tests;

use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class KernelTest extends KernelTestCase
{
    public function test(): void
    {
        $kernel = self::bootKernel();


        // debug log and dump
        //? $logger = (fn($v): \Psr\Log\LoggerInterface => $v)(static::getContainer()->get('logger'));
        //? $logger->debug('test', ['KernelTest']);


        $this->assertSame('test', $kernel->getEnvironment());

        /** @var ApiService $apiService */
        $apiService = static::getContainer()->get(ApiService::class);
        $this->assertNotEmpty($apiService->testHappyMessage());
    }
}
