<?php // $ bin/console make:test WebTestCase WebTest

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/index');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div > h3', 'Hello Symfony!');
        $this->assertSelectorTextContains('div > h3 + h3', 'Hello Stimulus!');
    }

    public function testSpa(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/spa');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div > h3', 'Hello Symfony!');
        $this->assertSelectorTextContains('div > h3 + h3', 'Hello React!');
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
        $this->assertObjectHasProperty('happyMessage', $data);
    }
}
