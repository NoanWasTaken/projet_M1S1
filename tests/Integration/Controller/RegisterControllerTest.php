<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class RegisterControllerTest extends WebTestCase
{
    public function testRegisterPageIsAccessible(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="registration_form[email]"]');
    }
}