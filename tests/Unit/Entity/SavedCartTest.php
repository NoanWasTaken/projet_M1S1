<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Products;
use App\Entity\SavedCart;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class SavedCartTest extends TestCase
{
    public function testIdIsNullByDefault(): void
    {
        $savedCart = new SavedCart();

        $this->assertNull($savedCart->getId());
    }

    public function testInitialProductsEmpty(): void
    {
        $savedCart = new SavedCart();

        $this->assertCount(0, $savedCart->getProducts());
    }

    public function testSetAndGetName(): void
    {
        $savedCart = new SavedCart();
        $savedCart->setName('Ma liste de Noël');

        $this->assertSame('Ma liste de Noël', $savedCart->getName());
    }

    public function testSetAndGetUser(): void
    {
        $savedCart = new SavedCart();
        $user = new User();
        $user->setEmail('user@example.com');

        $savedCart->setUser($user);

        $this->assertSame($user, $savedCart->getUser());
    }

    public function testSetUserNull(): void
    {
        $savedCart = new SavedCart();
        $savedCart->setUser(null);

        $this->assertNull($savedCart->getUser());
    }

    public function testAddProduct(): void
    {
        $savedCart = new SavedCart();
        $product = new Products();
        $product->setName('Manette');
        $product->setPrice(49.0);
        $product->setStock(8);

        $savedCart->addProduct($product);

        $this->assertCount(1, $savedCart->getProducts());
    }

    public function testAddProductNoDuplicate(): void
    {
        $savedCart = new SavedCart();
        $product = new Products();
        $product->setName('Manette');
        $product->setPrice(49.0);
        $product->setStock(8);

        $savedCart->addProduct($product);
        $savedCart->addProduct($product);

        $this->assertCount(1, $savedCart->getProducts());
    }

    public function testRemoveProduct(): void
    {
        $savedCart = new SavedCart();
        $product = new Products();
        $product->setName('Manette');
        $product->setPrice(49.0);
        $product->setStock(8);

        $savedCart->addProduct($product);
        $savedCart->removeProduct($product);

        $this->assertCount(0, $savedCart->getProducts());
    }
}
