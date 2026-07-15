<?php

namespace App\Tests\Integration\Controller;

use App\Entity\User;
use App\Tests\Integration\DatabaseSetupTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientProfileControllerTest extends WebTestCase
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

    private function createUser(string $email = 'profile@example.com'): User
    {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setName('Profile');
        $user->setSurname('Test');
        $user->setIsVerified(true);
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function testProfilePageRedirectsWhenNotAuthenticated(): void
    {
        $this->client->request('GET', '/profile');

        $this->assertResponseRedirects('/login');
    }

    public function testProfilePageIsAccessibleWhenAuthenticated(): void
    {
        $user = $this->createUser();

        $this->client->loginUser($user);
        $this->client->request('GET', '/profile');

        $this->assertResponseIsSuccessful();
    }
}
