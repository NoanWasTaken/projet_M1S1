<?php

namespace App\Controller;

use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\Security\OrderVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/orders', name: 'app_order_')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * Liste des commandes de l'utilisateur connecté.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $orders = $this->orderRepository->findByUser($this->getUser());

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    /**
     * Détail d'une commande — accès contrôlé par le OrderVoter.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Order $order): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::VIEW, $order);

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    /**
     * Annulation d'une commande — accès contrôlé par le OrderVoter.
     */
    #[Route('/{id}/cancel', name: 'cancel', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function cancel(Order $order): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::CANCEL, $order);

        $this->validateCsrf('cancel_order_' . $order->getId());

        $order->setStatus(OrderStatus::CANCELLED);
        $this->em->flush();

        $this->addFlash('success', 'Votre commande #' . $order->getId() . ' a bien été annulée.');

        return $this->redirectToRoute('app_order_index');
    }

    /**
     * Vérifie le token CSRF manuellement.
     */
    private function validateCsrf(string $tokenId): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $token   = $request->request->get('_token');

        if (!$this->isCsrfTokenValid($tokenId, $token)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
    }
}
