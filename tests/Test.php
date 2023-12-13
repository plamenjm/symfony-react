<?php // $ bin/console make:test TestCase Test

namespace App\Tests;

use App\Service\Utils;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
//use Psr\Log\LoggerInterface;

class Test extends TestCase
{
    public function test(): void
    {
        // debug log and dump
        //dump(['dump']); //var_dump(['var_dump']);
        //fwrite(STDERR, print_r(['STDERR'], TRUE)); //file_put_contents('php://stderr', print_r(['php://stderr'], TRUE));
        //dd(['dd']); //throw new \RuntimeException(print_r(['RuntimeException'], true));


        //$this->assertTrue(true);

        $utils = new Utils(new Logger('test'));
        $this->assertNotEmpty($utils->happyMessage());
    }
}
