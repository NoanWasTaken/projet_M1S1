<?php

namespace App\Tests\Unit\Entity;

use App\Entity\GameTypes;
use App\Entity\Products;
use App\Entity\Review;
use PHPUnit\Framework\TestCase;

class ProductsTest extends TestCase
{
    public function testProductInitialization(): void
    {
        $product = new Products();

        $this->assertNull($product->getId());
        $this->assertTrue($product->isAvailable());
        $this->assertNotNull($product->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $product->getCreatedAt());
        $this->assertCount(0, $product->getGameTypes());
        $this->assertCount(0, $product->getReviews());
    }

    public function testSetAndGetName(): void
    {
        $product = new Products();
        $product->setName('Clavier mécanique');

        $this->assertSame('Clavier mécanique', $product->getName());
    }

    public function testSetAndGetDescription(): void
    {
        $product = new Products();
        $product->setDescription('Un très bon clavier');

        $this->assertSame('Un très bon clavier', $product->getDescription());
    }

    public function testSetAndGetPrice(): void
    {
        $product = new Products();
        $product->setPrice(89.99);

        $this->assertEqualsWithDelta(89.99, $product->getPrice(), 0.001);
    }

    public function testSetAndGetStock(): void
    {
        $product = new Products();
        $product->setStock(50);

        $this->assertSame(50, $product->getStock());
    }

    public function testSetAndGetCategory(): void
    {
        $product = new Products();
        $product->setCategory('Claviers');

        $this->assertSame('Claviers', $product->getCategory());
    }

    public function testSetAndGetBrand(): void
    {
        $product = new Products();
        $product->setBrand('Logitech');

        $this->assertSame('Logitech', $product->getBrand());
    }

    public function testSetAndGetPhotos(): void
    {
        $product = new Products();
        $photos = ['photo1.jpg', 'photo2.jpg'];
        $product->setPhotos($photos);

        $this->assertSame($photos, $product->getPhotos());
    }

    public function testSetAndGetSpecifications(): void
    {
        $product = new Products();
        $specs = ['color' => 'black', 'weight' => '500g'];
        $product->setSpecifications($specs);

        $this->assertSame($specs, $product->getSpecifications());
    }

    public function testSetIsAvailable(): void
    {
        $product = new Products();
        $product->setIsAvailable(false);

        $this->assertFalse($product->isAvailable());
    }

    public function testSetAndGetRating(): void
    {
        $product = new Products();
        $product->setRating('4.5');

        $this->assertSame('4.5', $product->getRating());
    }

    public function testAddGameType(): void
    {
        $product = new Products();
        $gameType = new GameTypes();
        $gameType->setType('FPS');

        $product->addGameType($gameType);

        $this->assertCount(1, $product->getGameTypes());
    }

    public function testAddGameTypeNoDuplicate(): void
    {
        $product = new Products();
        $gameType = new GameTypes();
        $gameType->setType('FPS');

        $product->addGameType($gameType);
        $product->addGameType($gameType);

        $this->assertCount(1, $product->getGameTypes());
    }

    public function testRemoveGameType(): void
    {
        $product = new Products();
        $gameType = new GameTypes();
        $gameType->setType('FPS');

        $product->addGameType($gameType);
        $product->removeGameType($gameType);

        $this->assertCount(0, $product->getGameTypes());
    }

    public function testAddReview(): void
    {
        $product = new Products();
        $review = new Review();
        $review->setRating(4);

        $product->addReview($review);

        $this->assertCount(1, $product->getReviews());
        $this->assertSame($product, $review->getProduct());
    }

    public function testAddReviewNoDuplicate(): void
    {
        $product = new Products();
        $review = new Review();
        $review->setRating(5);

        $product->addReview($review);
        $product->addReview($review);

        $this->assertCount(1, $product->getReviews());
    }

    public function testRemoveReview(): void
    {
        $product = new Products();
        $review = new Review();
        $review->setRating(3);

        $product->addReview($review);
        $product->removeReview($review);

        $this->assertCount(0, $product->getReviews());
    }

    public function testComputeAverageRatingWithReviews(): void
    {
        $product = new Products();

        $r1 = new Review();
        $r1->setRating(4);
        $r1->setProduct($product);

        $r2 = new Review();
        $r2->setRating(2);
        $r2->setProduct($product);

        $product->addReview($r1);
        $product->addReview($r2);

        $product->computeAverageRating();

        $this->assertSame('3.0', $product->getRating());
    }

    public function testComputeAverageRatingNoReviews(): void
    {
        $product = new Products();
        $product->computeAverageRating();

        $this->assertNull($product->getRating());
    }

    public function testToString(): void
    {
        $product = new Products();
        $product->setName('Écran gaming');
        $product->setPrice(300.0);
        $product->setStock(10);

        $this->assertSame('Écran gaming', (string) $product);
    }

    public function testSetCreatedAt(): void
    {
        $product = new Products();
        $date = new \DateTimeImmutable('2025-01-15');
        $product->setCreatedAt($date);

        $this->assertSame($date, $product->getCreatedAt());
    }
}
