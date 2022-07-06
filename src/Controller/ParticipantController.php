<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Service\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/participant', name: 'participant_')]
class ParticipantController extends AbstractController
{

    #[Route('/{id}', name: 'profil', methods: ['GET'])]
    public function profil(int $id, ParticipantRepository $participantRepository): Response
    {
        if (!$this->getUser())
            throw new AccessDeniedException("Vous devez etre connecter!");

        $participant = $participantRepository->find($id);
        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/{id}/edit', name: 'editer_profil', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, ParticipantRepository $participantRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if (!$this->getUser())
            throw new AccessDeniedException("Vous devez etre connecter!");
            if ($this->getUser()->getId() != $id)
                throw new AccessDeniedException("Vous ne pouvez pas modifer un profil qui n'est pas le votre");



        $participant = $participantRepository->find($id);

        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $participant->setPassword(
                $userPasswordHasher->hashPassword(
                    $participant,
                    $form->get('plainPassword')->getData()
                )
            );
            $participantRepository->add($participant, true);

            return $this->redirectToRoute('participant_profil', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
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
