<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CartRepository $cartRepository
    ) {
    }

    /**
     * Récupérer ou créer le panier d'un utilisateur
     */
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

    /**
     * Ajouter un produit au panier
     */
    public function addProduct(User $user, Product $product, int $quantity = 1): void
    {
        $cart = $this->getOrCreateCart($user);

        // Vérifier si le produit existe déjà dans le panier
        $existingItem = null;
        foreach ($cart->getItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $existingItem = $item;
                break;
            }
        }

        if ($existingItem) {
            // Augmenter la quantité
            $existingItem->incrementQuantity($quantity);
        } else {
            // Créer un nouvel item
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

    /**
     * Mettre à jour la quantité d'un article
     */
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

    /**
     * Supprimer un produit du panier
     */
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

    /**
     * Vider complètement le panier
     */
    public function clearCart(User $user): void
    {
        $cart = $this->getOrCreateCart($user);
        
        foreach ($cart->getItems() as $item) {
            $this->entityManager->remove($item);
        }

        $cart->clear();
        $this->entityManager->flush();
    }

    /**
     * Obtenir le nombre d'articles dans le panier
     */
    public function getCartItemCount(User $user): int
    {
        $cart = $this->cartRepository->findByUser($user);
        
        if (!$cart) {
            return 0;
        }

        return $cart->getTotalItems();
    }
}
