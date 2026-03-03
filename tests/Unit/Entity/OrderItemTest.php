<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Products;
use PHPUnit\Framework\TestCase;

class OrderItemTest extends TestCase
{
    public function testDefaultQuantity(): void
    {
        $item = new OrderItem();

        $this->assertSame(1, $item->getQuantity());
    }

    public function testDefaultUnitPrice(): void
    {
        $item = new OrderItem();

        $this->assertEqualsWithDelta(0.0, $item->getUnitPrice(), 0.001);
    }

    public function testSetAndGetOrder(): void
    {
        $item = new OrderItem();
        $order = new Order();

        $item->setOrder($order);

        $this->assertSame($order, $item->getOrder());
    }

    public function testSetAndGetProduct(): void
    {
        $item = new OrderItem();
        $product = new Products();
        $product->setName('Souris gaming');
        $product->setPrice(69.99);
        $product->setStock(5);

        $item->setProduct($product);

        $this->assertSame($product, $item->getProduct());
    }

    public function testSetAndGetQuantity(): void
    {
        $item = new OrderItem();
        $item->setQuantity(3);

        $this->assertSame(3, $item->getQuantity());
    }

    public function testSetAndGetUnitPrice(): void
    {
        $item = new OrderItem();
        $item->setUnitPrice(29.99);

        $this->assertEqualsWithDelta(29.99, $item->getUnitPrice(), 0.001);
    }

    public function testGetSubtotal(): void
    {
        $item = new OrderItem();
        $item->setQuantity(4);
        $item->setUnitPrice(12.50);

        $this->assertEqualsWithDelta(50.0, $item->getSubtotal(), 0.001);
    }

    public function testToStringWithProduct(): void
    {
        $product = new Products();
        $product->setName('Casque');
        $product->setPrice(89.0);
        $product->setStock(2);

        $item = new OrderItem();
        $item->setProduct($product);
        $item->setQuantity(2);

        $this->assertSame('Casque × 2', (string) $item);
    }
}
