<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Enum\OrderStatus;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testDefaultStatus(): void
    {
        $order = new Order();

        $this->assertSame(OrderStatus::VALIDATED, $order->getStatus());
    }

    public function testDefaultTotal(): void
    {
        $order = new Order();

        $this->assertEqualsWithDelta(0.0, $order->getTotal(), 0.001);
    }

    public function testCreatedAtIsSetOnConstruct(): void
    {
        $order = new Order();

        $this->assertNotNull($order->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $order->getCreatedAt());
    }

    public function testSetAndGetUser(): void
    {
        $order = new Order();
        $user = new User();
        $user->setEmail('buyer@example.com');

        $order->setUser($user);

        $this->assertSame($user, $order->getUser());
    }

    public function testSetAndGetStatus(): void
    {
        $order = new Order();
        $order->setStatus(OrderStatus::CANCELLED);

        $this->assertSame(OrderStatus::CANCELLED, $order->getStatus());
    }

    public function testSetAndGetTotal(): void
    {
        $order = new Order();
        $order->setTotal(199.90);

        $this->assertEqualsWithDelta(199.90, $order->getTotal(), 0.001);
    }

    public function testAddItem(): void
    {
        $order = new Order();
        $item = new OrderItem();

        $order->addItem($item);

        $this->assertCount(1, $order->getItems());
        $this->assertSame($order, $item->getOrder());
    }

    public function testAddItemDoesNotDuplicate(): void
    {
        $order = new Order();
        $item = new OrderItem();

        $order->addItem($item);
        $order->addItem($item);

        $this->assertCount(1, $order->getItems());
    }

    public function testRemoveItem(): void
    {
        $order = new Order();
        $item = new OrderItem();

        $order->addItem($item);
        $order->removeItem($item);

        $this->assertCount(0, $order->getItems());
    }

    public function testSetCreatedAt(): void
    {
        $order = new Order();
        $date = new \DateTimeImmutable('2025-05-01');
        $order->setCreatedAt($date);

        $this->assertSame($date, $order->getCreatedAt());
    }
}
