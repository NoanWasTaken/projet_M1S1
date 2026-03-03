<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PublicPagesTest extends WebTestCase
{
    public function testRgpdPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/rgpd');

        $this->assertResponseIsSuccessful();
    }

    public function testLoginPageRendersForm(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }
}
