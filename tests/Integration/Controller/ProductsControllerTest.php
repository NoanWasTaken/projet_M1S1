<?php

namespace App\Tests\Integration\Controller;

use App\Entity\Products;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProductsControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

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

    private function createProduct(): Products
    {
        $product = new Products();
        $product->setName('Test Product');
        $product->setPrice(99.99);
        $product->setStock(10);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        return $product;
    }

    private function createUser(): User
    {
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail('testreviewer@example.com');
        $user->setName('Test');
        $user->setSurname('Reviewer');
        $user->setIsVerified(true);
        $user->setPassword($passwordHasher->hashPassword($user, 'password123'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function testCataloguePageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/catalogue');

        $this->assertResponseIsSuccessful();
    }

    public function testProductDetailPageReturns404ForNonExistentProduct(): void
    {
        $client = static::createClient();
        $client->request('GET', '/product/9999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testProductDetailPageIsAccessible(): void
    {
        $product = $this->createProduct();

        $client = static::createClient();
        $client->request('GET', '/product/' . $product->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testUnauthenticatedReviewSubmissionRedirectsToLogin(): void
    {
        $product = $this->createProduct();

        $client = static::createClient();
        $crawler = $client->request('GET', '/product/' . $product->getId());
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="review"]')->form([
            'review[rating]' => '5',
            'review[comment]' => 'Great product!',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/login');
    }

    public function testAuthenticatedUserCanSubmitReview(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();

        $client = static::createClient();
        $client->loginUser($user);

        $crawler = $client->request('GET', '/product/' . $product->getId());
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="review"]')->form([
            'review[rating]' => '5',
            'review[comment]' => 'Great product!',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/product/' . $product->getId());
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
    }
}
