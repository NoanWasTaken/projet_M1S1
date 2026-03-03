<?php

namespace App\Tests\Unit\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Products;
use App\Entity\User;
use App\Repository\CartRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
    public function testGetOrCreateCartReturnsExistingCart(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $existingCart = new Cart();
        $existingCart->setUser($user);

        $cartRepository = $this->createMock(CartRepository::class);
        $cartRepository->expects($this->once())
            ->method('findByUser')
            ->with($user)
            ->willReturn($existingCart);

        $em = $this->createStub(EntityManagerInterface::class);
        $cartService = new CartService($em, $cartRepository);

        $result = $cartService->getOrCreateCart($user);

        $this->assertSame($existingCart, $result);
    }

    public function testGetOrCreateCartCreatesNewCartWhenNone(): void
    {
        $user = new User();
        $user->setEmail('newuser@example.com');

        $cartRepository = $this->createMock(CartRepository::class);
        $cartRepository->expects($this->once())
            ->method('findByUser')
            ->with($user)
            ->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $cartService = new CartService($em, $cartRepository);
        $result = $cartService->getOrCreateCart($user);

        $this->assertInstanceOf(Cart::class, $result);
        $this->assertSame($user, $result->getUser());
    }

    public function testGetCartByShareToken(): void
    {
        $cart = new Cart();
        $cart->setShareToken('mytoken');

        $cartRepository = $this->createMock(CartRepository::class);
        $cartRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['shareToken' => 'mytoken'])
            ->willReturn($cart);

        $em = $this->createStub(EntityManagerInterface::class);
        $cartService = new CartService($em, $cartRepository);

        $result = $cartService->getCartByShareToken('mytoken');

        $this->assertSame($cart, $result);
    }

    public function testGetCartByShareTokenReturnsNullWhenNotFound(): void
    {
        $cartRepository = $this->createMock(CartRepository::class);
        $cartRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['shareToken' => 'unknown'])
            ->willReturn(null);

        $em = $this->createStub(EntityManagerInterface::class);
        $cartService = new CartService($em, $cartRepository);

        $result = $cartService->getCartByShareToken('unknown');

        $this->assertNull($result);
    }

    public function testEnsureShareTokenDoesNothingWhenTokenExists(): void
    {
        $cart = new Cart();
        $cart->setShareToken('existingtoken');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');

        $cartRepository = $this->createStub(CartRepository::class);
        $cartService = new CartService($em, $cartRepository);
        $cartService->ensureShareToken($cart);

        $this->assertSame('existingtoken', $cart->getShareToken());
    }

    public function testEnsureShareTokenGeneratesTokenWhenMissing(): void
    {
        $cart = new Cart();

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($cart);
        $em->expects($this->once())->method('flush');

        $cartRepository = $this->createStub(CartRepository::class);
        $cartService = new CartService($em, $cartRepository);
        $cartService->ensureShareToken($cart);

        $this->assertNotNull($cart->getShareToken());
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $cart->getShareToken());
    }

    public function testRemoveProductRemovesMatchingItem(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $product = $this->createStub(Products::class);
        $product->method('getId')->willReturn(7);
        $product->method('getPrice')->willReturn(50.0);

        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(2);

        $cart = new Cart();
        $cart->setUser($user);
        $cart->addItem($item);

        $cartRepository = $this->createMock(CartRepository::class);
        $cartRepository->expects($this->once())
            ->method('findByUser')
            ->with($user)
            ->willReturn($cart);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('remove')->with($item);
        $em->expects($this->once())->method('flush');

        $cartService = new CartService($em, $cartRepository);
        $cartService->removeProduct($user, 7);

        $this->assertCount(0, $cart->getItems());
    }

    public function testUpdateQuantityToZeroCallsRemove(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $product = $this->createStub(Products::class);
        $product->method('getId')->willReturn(3);
        $product->method('getPrice')->willReturn(20.0);

        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(1);

        $cart = new Cart();
        $cart->setUser($user);
        $cart->addItem($item);

        $cartRepository = $this->createMock(CartRepository::class);
        $cartRepository->method('findByUser')->with($user)->willReturn($cart);

        $em = $this->createStub(EntityManagerInterface::class);

        $cartService = new CartService($em, $cartRepository);
        $cartService->updateQuantity($user, 3, 0);

        $this->assertCount(0, $cart->getItems());
    }

    public function testUpdateQuantityUpdatesExistingItem(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');

        $product = $this->createStub(Products::class);
        $product->method('getId')->willReturn(5);
        $product->method('getPrice')->willReturn(30.0);

        $item = new CartItem();
        $item->setProduct($product);
        $item->setQuantity(1);

        $cart = new Cart();
        $cart->setUser($user);
        $cart->addItem($item);

        $cartRepository = $this->createMock(CartRepository::class);
        $cartRepository->expects($this->once())
            ->method('findByUser')
            ->with($user)
            ->willReturn($cart);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('flush');

        $cartService = new CartService($em, $cartRepository);
        $cartService->updateQuantity($user, 5, 4);

        $this->assertSame(4, $item->getQuantity());
    }
}
