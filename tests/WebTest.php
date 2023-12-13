<?php // $ bin/console make:test WebTestCase WebTest

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebTest extends WebTestCase
{
    public function testSpa(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/spa');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div > h1', 'Hello WebController.spa!');
        $this->assertSelectorTextContains('div ~ div > h1', 'Hello react_component!');
        $this->assertSelectorTextContains('div ~ div ~ div > h1', 'Hello data-controller!');
    }

    public function testApiParams(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/params', [
            'headers' => ['Accept' => 'application/json',]
        ]);

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();
        $content = $response->getContent();
        $data = json_decode($content);
        $this->assertObjectHasProperty('fullName', $data);
    }
}
