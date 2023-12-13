<?php // $ bin/console make:test ApiTestCase ApiTest

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class ApiTest extends ApiTestCase
{
    public function testParams(): void
    {
        $response = static::createClient()->request('GET', '/api/params');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['fullName' => 'ApiController']);
    }
}
