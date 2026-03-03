<?php

namespace App\Tests\Unit\Entity;

use App\Entity\GameTypes;
use App\Entity\Products;
use PHPUnit\Framework\TestCase;

class GameTypesTest extends TestCase
{
    public function testInitialProductsEmpty(): void
    {
        $gameType = new GameTypes();

        $this->assertCount(0, $gameType->getProducts());
    }

    public function testSetAndGetType(): void
    {
        $gameType = new GameTypes();
        $gameType->setType('FPS');

        $this->assertSame('FPS', $gameType->getType());
    }

    public function testToString(): void
    {
        $gameType = new GameTypes();
        $gameType->setType('MOBA');

        $this->assertSame('MOBA', (string) $gameType);
    }

    public function testAddProduct(): void
    {
        $gameType = new GameTypes();
        $product = new Products();
        $product->setName('Souris');
        $product->setPrice(59.0);
        $product->setStock(10);

        $gameType->addProduct($product);

        $this->assertCount(1, $gameType->getProducts());
    }

    public function testAddProductNoDuplicate(): void
    {
        $gameType = new GameTypes();
        $product = new Products();
        $product->setName('Souris');
        $product->setPrice(59.0);
        $product->setStock(10);

        $gameType->addProduct($product);
        $gameType->addProduct($product);

        $this->assertCount(1, $gameType->getProducts());
    }

    public function testRemoveProduct(): void
    {
        $gameType = new GameTypes();
        $product = new Products();
        $product->setName('Souris');
        $product->setPrice(59.0);
        $product->setStock(10);

        $gameType->addProduct($product);
        $gameType->removeProduct($product);

        $this->assertCount(0, $gameType->getProducts());
    }
}
