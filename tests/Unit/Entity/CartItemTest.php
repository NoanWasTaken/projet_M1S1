<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Products;
use PHPUnit\Framework\TestCase;

class CartItemTest extends TestCase
{
    public function testDefaultQuantityIsOne(): void
    {
        $item = new CartItem();

        $this->assertSame(1, $item->getQuantity());
    }

    public function testAddedAtIsSetOnConstruct(): void
    {
        $item = new CartItem();

        $this->assertNotNull($item->getAddedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $item->getAddedAt());
    }

    public function testSetAndGetProduct(): void
    {
        $item = new CartItem();
        $product = new Products();
        $product->setName('Clavier');
        $product->setPrice(49.99);
        $product->setStock(10);

        $item->setProduct($product);

        $this->assertSame($product, $item->getProduct());
    }

    public function testSetAndGetCart(): void
    {
        $item = new CartItem();
        $cart = new Cart();

        $item->setCart($cart);

        $this->assertSame($cart, $item->getCart());
    }

    public function testSetAndGetQuantity(): void
    {
        $item = new CartItem();
        $item->setQuantity(5);

        $this->assertSame(5, $item->getQuantity());
    }

    public function testGetSubtotal(): void
    {
        $product = new Products();
        $product->setPrice(15.0);

        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(4);

        $this->assertEqualsWithDelta(60.0, $item->getSubtotal(), 0.001);
    }

    public function testSetAddedAt(): void
    {
        $item = new CartItem();
        $date = new \DateTimeImmutable('2025-03-01');
        $item->setAddedAt($date);

        $this->assertSame($date, $item->getAddedAt());
    }
}
