<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    public function testLoginPageIsRendered(): void
    {
        // Client
        $client = static::createClient();

        //Rquest
        $client->request('GET', '/login');

        // Assertions HTTP
        $this->assertResponseIsSuccessful();
        
        // Assertions Contenu (mots clés)
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }
}
