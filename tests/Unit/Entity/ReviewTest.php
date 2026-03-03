<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Products;
use App\Entity\Review;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ReviewTest extends TestCase
{
    public function testCreatedAtIsSetOnConstruct(): void
    {
        $review = new Review();

        $this->assertNotNull($review->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $review->getCreatedAt());
    }

    public function testSetAndGetProduct(): void
    {
        $review = new Review();
        $product = new Products();
        $product->setName('Casque');
        $product->setPrice(99.0);
        $product->setStock(5);

        $review->setProduct($product);

        $this->assertSame($product, $review->getProduct());
    }

    public function testSetAndGetRating(): void
    {
        $review = new Review();
        $review->setRating(5);

        $this->assertSame(5, $review->getRating());
    }

    public function testSetAndGetComment(): void
    {
        $review = new Review();
        $review->setComment('Excellent produit !');

        $this->assertSame('Excellent produit !', $review->getComment());
    }

    public function testSetAndGetAuthor(): void
    {
        $review = new Review();
        $user = new User();
        $user->setEmail('reviewer@example.com');
        $user->setName('Alice');
        $user->setSurname('Dupont');

        $review->setAuthor($user);

        $this->assertSame($user, $review->getAuthor());
    }

    public function testGetAuthorNameWithUser(): void
    {
        $user = new User();
        $user->setEmail('reviewer@example.com');
        $user->setName('Alice');
        $user->setSurname('Dupont');

        $review = new Review();
        $review->setAuthor($user);

        $this->assertSame('Alice Dupont', $review->getAuthorName());
    }

    public function testGetAuthorNameWithAuthorNameFallback(): void
    {
        $review = new Review();
        $review->setAuthorName('Jean');

        $this->assertSame('Jean', $review->getAuthorName());
    }

    public function testGetAuthorNameDefaultAnonymous(): void
    {
        $review = new Review();

        $this->assertSame('Anonyme', $review->getAuthorName());
    }

    public function testSetAuthorNameNull(): void
    {
        $review = new Review();
        $review->setAuthorName(null);

        $this->assertSame('Anonyme', $review->getAuthorName());
    }

    public function testSetAuthor(): void
    {
        $review = new Review();
        $review->setAuthor(null);

        $this->assertNull($review->getAuthor());
    }
}
