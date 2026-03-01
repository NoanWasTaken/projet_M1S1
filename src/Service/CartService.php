<?php
namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Products;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
        public function importCartItems(Cart $from, Cart $to): void
        {
            foreach ($from->getItems() as $item) {
                $found = false;
                foreach ($to->getItems() as $toItem) {
                    if ($toItem->getProduct()->getId() === $item->getProduct()->getId()) {
                        $toItem->setQuantity($toItem->getQuantity() + $item->getQuantity());
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $newItem = new CartItem();
                    $newItem->setCart($to);
                    $newItem->setProduct($item->getProduct());
                    $newItem->setQuantity($item->getQuantity());
                    $to->addItem($newItem);
                    $this->entityManager->persist($newItem);
                }
            }
            $to->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }
    public function getCartByShareToken(string $token): ?Cart
    {
        return $this->cartRepository->findOneBy(['shareToken' => $token]);
    }
    public function ensureShareToken(Cart $cart): void
    {
        if (!$cart->getShareToken()) {
            $token = bin2hex(random_bytes(32));
            $cart->setShareToken($token);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }
    }
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CartRepository $cartRepository
    ) {
    }

    public function getOrCreateCart(User $user): Cart
    {
        $cart = $this->cartRepository->findByUser($user);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }
        return $cart;
    }

    public function addProduct(User $user, Products $product, int $quantity = 1): void
    {
        $cart = $this->getOrCreateCart($user);
        $existingItem = null;
        foreach ($cart->getItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $existingItem = $item;
                break;
            }
        }
        if ($existingItem) {
            $existingItem->incrementQuantity($quantity);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cart->addItem($cartItem);
            $this->entityManager->persist($cartItem);
        }
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    public function updateQuantity(User $user, int $productId, int $quantity): void
    {
        $cart = $this->getOrCreateCart($user);
        foreach ($cart->getItems() as $item) {
            if ($item->getProduct()->getId() === $productId) {
                if ($quantity <= 0) {
                    $this->removeProduct($user, $productId);
                } else {
                    $item->setQuantity($quantity);
                    $cart->setUpdatedAt(new \DateTimeImmutable());
                    $this->entityManager->flush();
                }
                return;
            }
        }
    }

    public function removeProduct(User $user, int $productId): void
    {
        $cart = $this->getOrCreateCart($user);
        foreach ($cart->getItems() as $item) {
            if ($item->getProduct()->getId() === $productId) {
                $cart->removeItem($item);
                $this->entityManager->remove($item);
                $cart->setUpdatedAt(new \DateTimeImmutable());
                $this->entityManager->flush();
                return;
            }
        }
    }

    public function clearCart(User $user): void
    {
        $cart = $this->getOrCreateCart($user);
        foreach ($cart->getItems() as $item) {
            $this->entityManager->remove($item);
        }
        $cart->clear();
        $this->entityManager->flush();
    }

    public function getCartItemCount(User $user): int
    {
        $cart = $this->cartRepository->findByUser($user);
        if (!$cart) {
            return 0;
        }
        return $cart->getTotalItems();
    }
}
