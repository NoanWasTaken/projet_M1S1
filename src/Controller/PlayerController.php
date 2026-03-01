<?php
namespace App\Controller;

use App\Entity\ProPlayer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private \App\Service\CartService $cartService,
    ) {}
    #[Route('/player/{id}', name: 'player_show')]
    public function show(int $id): Response
    {
        $player = $this->em->getRepository(ProPlayer::class)->find($id);
        if (!$player) {
            throw $this->createNotFoundException('Joueur non trouvé');
        }
        $cart = null;
        if ($this->getUser()) {
            $cart = $this->cartService->getOrCreateCart($this->getUser());
        }
        return $this->render('player/show.html.twig', [
            'player' => $player,
            'cart' => $cart,
        ]);
    }

    #[Route('/player/{id}/add/{type}', name: 'player_add_product', methods: ['POST'])]
    public function addProduct(int $id, string $type, \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage): Response
    {
        $player = $this->em->getRepository(ProPlayer::class)->find($id);
        if (!$player) {
            throw $this->createNotFoundException('Joueur non trouvé');
        }
        $user = $tokenStorage->getToken()->getUser();
        $productName = null;
        if ($type === 'mouse') $productName = $player->getMouse();
        elseif ($type === 'keyboard') $productName = $player->getKeyboard();
        elseif ($type === 'headset') $productName = $player->getHeadset();
        if ($productName) {
            $product = $this->em->getRepository(\App\Entity\Products::class)->findOneBy(['name' => $productName]);
            if ($product) {
                $this->cartService->addProduct($user, $product, 1);
            }
        }
        return $this->redirectToRoute('player_show', ['id' => $id]);
    }

    #[Route('/player/{id}/add-config', name: 'player_add_config', methods: ['POST'])]
    public function addConfig(int $id, \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage): Response
    {
        $player = $this->em->getRepository(ProPlayer::class)->find($id);
        if (!$player) {
            throw $this->createNotFoundException('Joueur non trouvé');
        }
        $user = $tokenStorage->getToken()->getUser();
        $products = [];
        foreach (['mouse', 'keyboard', 'headset'] as $type) {
            $productName = null;
            if ($type === 'mouse') $productName = $player->getMouse();
            elseif ($type === 'keyboard') $productName = $player->getKeyboard();
            elseif ($type === 'headset') $productName = $player->getHeadset();
            if ($productName) {
                $product = $this->em->getRepository(\App\Entity\Products::class)->findOneBy(['name' => $productName]);
                if ($product) {
                    $this->cartService->addProduct($user, $product, 1);
                }
            }
        }
        return $this->redirectToRoute('player_show', ['id' => $id]);
    }
}
