<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Service\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/participant', name: 'participant_')]
class ParticipantController extends AbstractController
{

    #[Route('/profil', name: 'profil', methods: ['GET'])]
    public function profil(): Response
    {
        if (!$this->getUser())
            throw new AccessDeniedException("Vous devez etre connecter!");


        return $this->render('participant/show.html.twig', [
            'participant' => $this->getUser(),
        ]);
    }

    #[Route('/edit', name: 'editer_profil', methods: ['GET', 'POST'])]
    public function edit(Request $request, ParticipantRepository $participantRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $participant = $this->getUser();
        if (!$participant)
            throw new AccessDeniedException("Vous devez etre connecter!");


        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$userPasswordHasher->isPasswordValid($participant, $form->get('actualPassword')->getData())) {
                $form->get('actualPassword')->addError(new FormError('Mot de passe actuel incorrect'));
                return $this->renderForm('participant/edit.html.twig', [
                    'participant' => $participant,
                    'form' => $form,
                ]);
            }

            $participant->setPassword(
                $userPasswordHasher->hashPassword(
                    $participant,
                    $form->get('plainPassword')->getData()
                )
            );
            $participantRepository->add($participant, true);

            return $this->redirectToRoute('participant_profil', [
                'participant' => $participant,
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/inscription/{id}', name: 'inscription')]
    public function inscription(Sortie $sortie, InscriptionService $inscriptionService, EntityManagerInterface $entityManager): Response
    {
        $participants = $inscriptionService->inscrireParticipant($sortie, $this->getUser(), $entityManager);
        $array = [];
        foreach ($participants as $participant) {
            $array[] = $participant;
        }
        return $this->json($array, 200, [], ['groups' => 'test']);
    }

    #[Route('/desinscription/{id}', name: 'desinscription')]
    public function desinscription(Sortie $sortie, InscriptionService $inscriptionService, EntityManagerInterface $entityManager): Response
    {
        $participants = $inscriptionService->desinscrireParticipant($sortie, $this->getUser(), $entityManager);
        $array = [];
        foreach ($participants as $participant) {
            $array[] = $participant;
        }
        return $this->json($array, 200, [], ['groups' => 'test']);
    }
}
