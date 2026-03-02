<?php
namespace App\Controller;

use App\Entity\SavedCart;
use App\Form\SavedCartType;
use App\Repository\SavedCartRepository;
use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SavedCartController extends AbstractController
{
    public function __construct(private \App\Service\CartService $cartService) {}
    #[Route('/cart/save', name: 'cart_save', methods: ['POST'])]
    public function save(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SavedCartType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SavedCart $savedCart */
            $savedCart = $form->getData();
            $savedCart->setUser($this->getUser());
            $user = $this->getUser();
            if ($user) {
                $cart = $this->cartService->getOrCreateCart($user);
                foreach ($cart->getItems() as $item) {
                    $savedCart->addProduct($item->getProduct());
                }
            }
            $em->persist($savedCart);
            $em->flush();
            $this->addFlash('success', 'Panier enregistré !');
            return $this->redirectToRoute('app_cart');
        }
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/load/{id}', name: 'cart_load', methods: ['POST'])]
    public function load(SavedCart $savedCart, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non connecté.');
            return $this->redirectToRoute('app_cart');
        }
    
        $this->cartService->clearCart($user);
        foreach ($savedCart->getProducts() as $product) {
            $this->cartService->addProduct($user, $product, 1); 
        }
        $this->addFlash('success', 'Panier chargé !');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/delete/{id}', name: 'cart_delete', methods: ['POST'])]
    public function delete(SavedCart $savedCart, EntityManagerInterface $em): Response
    {
        $em->remove($savedCart);
        $em->flush();
        return $this->redirectToRoute('app_client_profile');
    }
}
