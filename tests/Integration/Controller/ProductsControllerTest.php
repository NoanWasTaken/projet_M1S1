<?php

namespace App\Tests\Integration\Controller;

use App\Entity\Products;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProductsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
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
        $this->client->request('GET', '/catalogue');

        $this->assertResponseIsSuccessful();
    }

    public function testProductDetailPageReturns404ForNonExistentProduct(): void
    {
        $this->client->request('GET', '/product/9999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testProductDetailPageIsAccessible(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();

        $this->client->loginUser($user);
        $this->client->request('GET', '/product/' . $product->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testUnauthenticatedReviewSubmissionRedirectsToLogin(): void
    {
        $product = $this->createProduct();

        $this->client->request('GET', '/product/' . $product->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('form[name="review"]');
        $this->assertSelectorExists('a[href$="/login"]');
    }

    public function testAuthenticatedUserCanSubmitReview(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();

        $this->client->loginUser($user);

        $crawler = $this->client->request('GET', '/product/' . $product->getId());
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="review"]')->form([
            'review[rating]' => '5',
            'review[comment]' => 'Great product!',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/product/' . $product->getId());
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }
}
