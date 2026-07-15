<?php

namespace App\Tests\Integration\Controller;

use App\Entity\User;
use App\Tests\Integration\DatabaseSetupTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CartControllerTest extends WebTestCase
{
    use DatabaseSetupTrait;

    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->setUpDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    private function createUser(string $email = 'cartuser@example.com'): User
    {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setName('Cart');
        $user->setSurname('User');
        $user->setIsVerified(true);
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function testCartPageRedirectsWhenNotAuthenticated(): void
    {
        $this->client->request('GET', '/cart');

        $this->assertResponseRedirects('/login');
    }

    public function testCartPageIsAccessibleWhenAuthenticated(): void
    {
        $user = $this->createUser();

        $this->client->loginUser($user);
        $this->client->request('GET', '/cart');

        $this->assertResponseIsSuccessful();
    }

    public function testSharedCartViewNotFoundForInvalidToken(): void
    {
        $user = $this->createUser('sharedtest@example.com');
        $this->client->loginUser($user);
        $this->client->request('GET', '/cart/shared/invalidtoken123');

        $this->assertResponseStatusCodeSame(404);
    }
}
