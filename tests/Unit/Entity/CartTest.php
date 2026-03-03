<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Products;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    public function testCartInitialization(): void
    {
        $cart = new Cart();

        $this->assertNull($cart->getId());
        $this->assertNotNull($cart->getCreatedAt());
        $this->assertNotNull($cart->getUpdatedAt());
        $this->assertCount(0, $cart->getItems());
        $this->assertNull($cart->getShareToken());
    }

    public function testSetAndGetUser(): void
    {
        $cart = new Cart();
        $user = new User();
        $user->setEmail('test@example.com');

        $cart->setUser($user);

        $this->assertSame($user, $cart->getUser());
    }

    public function testAddItem(): void
    {
        $cart = new Cart();
        $item = new CartItem();

        $cart->addItem($item);

        $this->assertCount(1, $cart->getItems());
        $this->assertSame($cart, $item->getCart());
    }

    public function testAddItemDoesNotDuplicate(): void
    {
        $cart = new Cart();
        $item = new CartItem();

        $cart->addItem($item);
        $cart->addItem($item);

        $this->assertCount(1, $cart->getItems());
    }

    public function testRemoveItem(): void
    {
        $cart = new Cart();
        $item = new CartItem();

        $cart->addItem($item);
        $cart->removeItem($item);

        $this->assertCount(0, $cart->getItems());
    }

    public function testGetTotalWithItems(): void
    {
        $product = new Products();
        $product->setPrice(25.0);

        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(3);

        $cart = new Cart();
        $cart->addItem($item);

        $this->assertEqualsWithDelta(75.0, $cart->getTotal(), 0.001);
    }

    public function testGetTotalEmptyCart(): void
    {
        $cart = new Cart();

        $this->assertEqualsWithDelta(0.0, $cart->getTotal(), 0.001);
    }

    public function testGetTotalItems(): void
    {
        $product1 = new Products();
        $product1->setPrice(10.0);

        $product2 = new Products();
        $product2->setPrice(20.0);

        $item1 = new CartItem();
        $item1->setProduct($product1);
        $item1->setQuantity(2);

        $item2 = new CartItem();
        $item2->setProduct($product2);
        $item2->setQuantity(3);

        $cart = new Cart();
        $cart->addItem($item1);
        $cart->addItem($item2);

        $this->assertSame(5, $cart->getTotalItems());
    }

    public function testClearCart(): void
    {
        $product = new Products();
        $product->setPrice(10.0);

        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(1);

        $cart = new Cart();
        $cart->addItem($item);
        $cart->clear();

        $this->assertCount(0, $cart->getItems());
    }

    public function testShareToken(): void
    {
        $cart = new Cart();
        $cart->setShareToken('abc123token');

        $this->assertSame('abc123token', $cart->getShareToken());
    }

    public function testSetShareTokenNull(): void
    {
        $cart = new Cart();
        $cart->setShareToken('token');
        $cart->setShareToken(null);

        $this->assertNull($cart->getShareToken());
    }

    public function testSetCreatedAt(): void
    {
        $cart = new Cart();
        $date = new \DateTimeImmutable('2025-01-01 10:00:00');
        $cart->setCreatedAt($date);

        $this->assertSame($date, $cart->getCreatedAt());
    }

    public function testSetUpdatedAt(): void
    {
        $cart = new Cart();
        $date = new \DateTimeImmutable('2025-06-01 12:00:00');
        $cart->setUpdatedAt($date);

        $this->assertSame($date, $cart->getUpdatedAt());
    }
}
