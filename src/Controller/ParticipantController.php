<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Service\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/participant', name: 'participant_')]
class ParticipantController extends AbstractController
{

    #[Route('/{id}', name: 'profil')]
    public function profil(Participant $participant): Response
    {
        return $this->render('participant/profil.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/inscription/{id}', name: 'inscription')]
    public function inscription(Sortie $sortie, InscriptionService $inscriptionService, EntityManagerInterface $entityManager): Response
    {
        $inscriptionService->inscrireParticipant($sortie, $this->getUser(), $entityManager);

        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }

    #[Route('/desinscription/{id}', name: 'desinscription')]
    public function desinscription(Sortie $sortie, InscriptionService $inscriptionService, EntityManagerInterface $entityManager): Response
    {
        $inscriptionService->desinscrireParticipant($sortie, $this->getUser(), $entityManager);

        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }
}
