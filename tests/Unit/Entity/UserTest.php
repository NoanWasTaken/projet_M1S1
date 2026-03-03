<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Cart;
use App\Entity\ChatConversation;
use App\Entity\GameTypes;
use App\Entity\Order;
use App\Entity\PlayerProfile;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserEmailGetterAndSetter(): void
    {
        $user = new User();
        $email = 'test@example.com';

        $user->setEmail($email);

        $this->assertSame($email, $user->getEmail());
    }

    public function testIdIsNullByDefault(): void
    {
        $user = new User();

        $this->assertNull($user->getId());
    }

    public function testIsVerifiedFalseByDefault(): void
    {
        $user = new User();

        $this->assertFalse($user->isVerified());
    }

    public function testSetIsVerified(): void
    {
        $user = new User();
        $user->setIsVerified(true);

        $this->assertTrue($user->isVerified());
    }

    public function testSetAndGetName(): void
    {
        $user = new User();
        $user->setName('Alice');

        $this->assertSame('Alice', $user->getName());
    }

    public function testSetAndGetSurname(): void
    {
        $user = new User();
        $user->setSurname('Dupont');

        $this->assertSame('Dupont', $user->getSurname());
    }

    public function testSetAndGetPassword(): void
    {
        $user = new User();
        $user->setPassword('hashed_password');

        $this->assertSame('hashed_password', $user->getPassword());
    }

    public function testGetUserIdentifier(): void
    {
        $user = new User();
        $user->setEmail('id@example.com');

        $this->assertSame('id@example.com', $user->getUserIdentifier());
    }

    public function testGetRolesAlwaysIncludesRoleUser(): void
    {
        $user = new User();

        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testSetAndGetRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testToString(): void
    {
        $user = new User();
        $user->setName('Alice');
        $user->setSurname('Dupont');

        $this->assertSame('Alice Dupont', (string) $user);
    }

    public function testToStringFallsBackToEmail(): void
    {
        $user = new User();
        $user->setEmail('fallback@example.com');

        $this->assertSame('fallback@example.com', (string) $user);
    }

    public function testInitialCollectionsAreEmpty(): void
    {
        $user = new User();

        $this->assertCount(0, $user->getOrders());
        $this->assertCount(0, $user->getChatConversations());
        $this->assertCount(0, $user->getSavedCarts());
        $this->assertCount(0, $user->getGameTypes());
    }

    public function testAddOrder(): void
    {
        $user = new User();
        $order = new Order();

        $user->addOrder($order);

        $this->assertCount(1, $user->getOrders());
        $this->assertSame($user, $order->getUser());
    }

    public function testAddOrderNoDuplicate(): void
    {
        $user = new User();
        $order = new Order();

        $user->addOrder($order);
        $user->addOrder($order);

        $this->assertCount(1, $user->getOrders());
    }

    public function testRemoveOrder(): void
    {
        $user = new User();
        $order = new Order();

        $user->addOrder($order);
        $user->removeOrder($order);

        $this->assertCount(0, $user->getOrders());
    }

    public function testAddGameType(): void
    {
        $user = new User();
        $gameType = new GameTypes();
        $gameType->setType('RTS');

        $user->addGameType($gameType);

        $this->assertCount(1, $user->getGameTypes());
    }

    public function testRemoveGameType(): void
    {
        $user = new User();
        $gameType = new GameTypes();
        $gameType->setType('RTS');

        $user->addGameType($gameType);
        $user->removeGameType($gameType);

        $this->assertCount(0, $user->getGameTypes());
    }

    public function testAddChatConversation(): void
    {
        $user = new User();
        $conv = new ChatConversation();

        $user->addChatConversation($conv);

        $this->assertCount(1, $user->getChatConversations());
        $this->assertSame($user, $conv->getUser());
    }

    public function testPlayerProfileIsNullByDefault(): void
    {
        $user = new User();

        $this->assertNull($user->getPlayerProfile());
    }
}

