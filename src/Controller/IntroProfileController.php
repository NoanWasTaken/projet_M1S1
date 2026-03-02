<?php
namespace App\Controller;

use App\Entity\IntroProfileAnswer;
use App\Repository\IntroProfileAnswerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class IntroProfileController extends AbstractController
{
    #[Route('/intro-profile/answer', name: 'intro_profile_answer', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function answer(Request $request, EntityManagerInterface $em, IntroProfileAnswerRepository $repo): Response
    {
        $user = $this->getUser();
        $type = $request->request->get('gameType');
        if (!$type) {
            return $this->json(['error' => 'Missing game type'], 400);
        }
        $existing = $repo->findByUser($user);
        if ($existing) {
            $existing->setGameType($type);
            $em->flush();
        } else {
            $answer = new IntroProfileAnswer();
            $answer->setUser($user);
            $answer->setGameType($type);
            $em->persist($answer);
            $em->flush();
        }
        return $this->json(['success' => true]);
    }
}
