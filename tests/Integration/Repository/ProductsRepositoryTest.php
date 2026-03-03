<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductsRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ProductsRepository $repository;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = $this->entityManager->getRepository(Products::class);

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

    private function createProduct(string $name, float $price, int $stock, bool $available = true, string $category = 'Claviers'): Products
    {
        $product = new Products();
        $product->setName($name);
        $product->setPrice($price);
        $product->setStock($stock);
        $product->setIsAvailable($available);
        $product->setCategory($category);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    public function testFindAvailableProductsReturnsOnlyAvailableWithStock(): void
    {
        $this->createProduct('Clavier A', 79.0, 5, true);
        $this->createProduct('Clavier B', 49.0, 0, true);
        $this->createProduct('Clavier C', 99.0, 3, false);

        $results = $this->repository->findAvailableProducts();

        $this->assertCount(1, $results);
        $this->assertSame('Clavier A', $results[0]->getName());
    }

    public function testFindByCategoryReturnsOnlyMatchingAndAvailable(): void
    {
        $this->createProduct('Souris X', 39.0, 10, true, 'Souris');
        $this->createProduct('Clavier Y', 59.0, 8, true, 'Claviers');
        $this->createProduct('Souris Z', 29.0, 0, true, 'Souris');

        $results = $this->repository->findByCategory('Souris');

        $this->assertCount(1, $results);
        $this->assertSame('Souris X', $results[0]->getName());
    }

    public function testSearchProductsReturnsMatchingResults(): void
    {
        $this->createProduct('Casque Arctis 7', 149.0, 5, true, 'Casques');
        $this->createProduct('Souris G502', 99.0, 12, true, 'Souris');
        $this->createProduct('Tapis de souris XXL', 39.0, 20, true, 'Accessoires');

        $results = $this->repository->searchProducts('souris');

        $this->assertCount(2, $results);
    }

    public function testFindAvailableProductsReturnsEmptyWhenNone(): void
    {
        $results = $this->repository->findAvailableProducts();

        $this->assertCount(0, $results);
    }

    public function testGetAvailableCategories(): void
    {
        $this->createProduct('Produit A', 10.0, 5, true, 'Claviers');
        $this->createProduct('Produit B', 20.0, 3, true, 'Souris');
        $this->createProduct('Produit C', 30.0, 1, true, 'Claviers');

        $categories = $this->repository->getAvailableCategories();

        $this->assertContains('Claviers', $categories);
        $this->assertContains('Souris', $categories);
    }
}
