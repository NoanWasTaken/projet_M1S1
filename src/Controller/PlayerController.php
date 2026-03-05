<?php
namespace App\Controller;

use App\Entity\ProPlayer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
            'showIntroDialogue' => false,
        ]);
    }

    #[Route('/player/{id}/add/{type}', name: 'player_add_product', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addProduct(int $id, string $type, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('player_add_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('player_show', ['id' => $id]);
        }
        $player = $this->em->getRepository(ProPlayer::class)->find($id);
        if (!$player) {
            throw $this->createNotFoundException('Joueur non trouvé');
        }
        $user = $this->getUser();
        $productName = match ($type) {
            'mouse'    => $player->getMouse(),
            'keyboard' => $player->getKeyboard(),
            'headset'  => $player->getHeadset(),
            default    => null,
        };
        if ($productName) {
            $product = $this->em->getRepository(\App\Entity\Products::class)->findOneByNameInsensitive($productName);
            if ($product) {
                $this->cartService->addProduct($user, $product, 1);
                $this->addFlash('success', sprintf('"%s" ajouté au panier.', $product->getName()));
            } else {
                $this->addFlash('warning', sprintf('Produit "%s" introuvable dans notre catalogue.', $productName));
            }
        }
        return $this->redirectToRoute('player_show', ['id' => $id]);
    }

    #[Route('/player/{id}/add-config', name: 'player_add_config', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addConfig(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('player_add_config_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('player_show', ['id' => $id]);
        }
        $player = $this->em->getRepository(ProPlayer::class)->find($id);
        if (!$player) {
            throw $this->createNotFoundException('Joueur non trouvé');
        }
        $user = $this->getUser();
        $added = [];
        $missing = [];
        $productRepo = $this->em->getRepository(\App\Entity\Products::class);
        foreach ([
            'mouse'    => $player->getMouse(),
            'keyboard' => $player->getKeyboard(),
            'headset'  => $player->getHeadset(),
        ] as $type => $productName) {
            if (!$productName) {
                continue;
            }
            $product = $productRepo->findOneByNameInsensitive($productName);
            if ($product) {
                $this->cartService->addProduct($user, $product, 1);
                $added[] = $product->getName();
            } else {
                $missing[] = $productName;
            }
        }
        if ($added) {
            $this->addFlash('success', sprintf('%d produit(s) ajouté(s) au panier : %s.', count($added), implode(', ', $added)));
        }
        if ($missing) {
            $this->addFlash('warning', sprintf('Produit(s) introuvable(s) dans le catalogue : %s.', implode(', ', $missing)));
        }
        return $this->redirectToRoute('player_show', ['id' => $id]);
    }
}
