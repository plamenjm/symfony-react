<?php // $ bin/console make:test TestCase Test

namespace App\Tests;

use App\Service\ApiService;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    public function test(): void
    {
        //$logger = new \Monolog\Logger('test');
        //$logger->pushHandler(new \Monolog\Handler\TestHandler());

        // testDump
        //\App\TestDump::dd('test');
        //\App\TestDump::exception('test');
        \App\TestDump::varDump('test');
        \App\TestDump::stdErr('test');
        \App\TestDump::dump('test');
        //\App\TestDump::logger('test', $logger); //? Not working


        //$this->assertTrue(true);

        $apiService = new ApiService(); //ApiService(fn() => $logger)
        $this->assertNotEmpty($apiService->testHappyMessage());
    }
}
