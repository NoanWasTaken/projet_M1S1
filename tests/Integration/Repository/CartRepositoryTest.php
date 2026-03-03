<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Cart;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CartRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private CartRepository $repository;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = $this->entityManager->getRepository(Cart::class);

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

    private function createUser(string $email = 'cartrepouser@example.com'): User
    {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setName('Cart');
        $user->setSurname('Repo');
        $user->setIsVerified(true);
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function testFindByUserReturnsNullWhenNoCart(): void
    {
        $user = $this->createUser();

        $result = $this->repository->findByUser($user);

        $this->assertNull($result);
    }

    public function testFindByUserReturnsCartWhenExists(): void
    {
        $user = $this->createUser();

        $cart = new Cart();
        $cart->setUser($user);

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        $result = $this->repository->findByUser($user);

        $this->assertInstanceOf(Cart::class, $result);
        $this->assertSame($user->getEmail(), $result->getUser()->getEmail());
    }

    public function testFindByUserDoesNotReturnOtherUsersCart(): void
    {
        $user1 = $this->createUser('user1@example.com');
        $user2 = $this->createUser('user2@example.com');

        $cart = new Cart();
        $cart->setUser($user1);

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        $result = $this->repository->findByUser($user2);

        $this->assertNull($result);
    }
}
