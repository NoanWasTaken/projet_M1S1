<?php

namespace App\Tests\Integration\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class OrderControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    private function createUser(string $email = 'orderuser@example.com'): User
    {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setName('Order');
        $user->setSurname('User');
        $user->setIsVerified(true);
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createOrder(User $user, OrderStatus $status = OrderStatus::VALIDATED): Order
    {
        $order = new Order();
        $order->setUser($user);
        $order->setTotal(50.0);
        $order->setStatus($status);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    public function testOrdersPageRedirectsWhenNotAuthenticated(): void
    {
        $this->client->request('GET', '/orders');

        $this->assertResponseRedirects('/login');
    }

    public function testOrdersIndexIsAccessibleWhenAuthenticated(): void
    {
        $user = $this->createUser();

        $this->client->loginUser($user);
        $this->client->request('GET', '/orders');

        $this->assertResponseIsSuccessful();
    }

    public function testOrderDetailIsAccessibleByOwner(): void
    {
        $user = $this->createUser();
        $order = $this->createOrder($user);

        $this->client->loginUser($user);
        $this->client->request('GET', '/orders/' . $order->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testOrderDetailIsDeniedForOtherUser(): void
    {
        $owner = $this->createUser('owner@example.com');
        $other = $this->createUser('other@example.com');
        $order = $this->createOrder($owner);

        $this->client->loginUser($other);
        $this->client->request('GET', '/orders/' . $order->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testOrderDetailReturns404ForNonExistent(): void
    {
        $user = $this->createUser();

        $this->client->loginUser($user);
        $this->client->request('GET', '/orders/99999');

        $this->assertResponseStatusCodeSame(404);
    }
}
