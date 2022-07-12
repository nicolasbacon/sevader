<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\CSVType;
use App\Form\ParticipantType;
use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use App\Service\AjoutParticipant;
use App\Service\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/participant', name: 'participant_')]
class ParticipantController extends AbstractController
{
    #[Route('/list', name: 'list')]
    public function list(ParticipantRepository $participantRepository, Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $participants = $participantRepository->findAllOrderedByName();

        return $this->render('participant/list.html.twig', [
            'participants' => $participants
        ]);

    }

    #[Route('/delete/{id}', name: 'delete', requirements: ["id" => "\d+"])]
    public function delete(ParticipantRepository $participantRepository, int $id): Response
    {
        $participant = $participantRepository->find($id);

        if (!$participant) {
            throw $this->createNotFoundException("Oups, ce participant n'existe pas");
        }

        $participantRepository->remove($participant, true);

        $this->addFlash('success', 'Participant supprimé');

        return $this->redirectToRoute('participant_list');
    }

    #[Route('/activation/{id}', name: 'activation', requirements: ["id" => "\d+"])]
    public function inactivate(ParticipantRepository $participantRepository, int $id): Response
    {
        $participant = $participantRepository->find($id);

        if (!$participant) {
            throw $this->createNotFoundException("Oups, ce participant n'existe pas");
        }
        if ($participant->isActif()) {
            $participant->setActif(false);
        } else {
            $participant->setActif(true);
        }

        $participantRepository->add($participant, true);

        $this->addFlash('success', 'Participant désactivé');

        return $this->redirectToRoute('participant_list');
    }


    #[Route('/new', name: 'new')]
    public function new(ParticipantRepository $participantRepository, Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $participant = new Participant();
        $registrationForm = $this->createForm(RegistrationFormType::class, $participant);

        $registrationForm->handleRequest($request);

        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {

            $participant->setPassword(
                $userPasswordHasher->hashPassword(
                    $participant,
                    $registrationForm->get('plainPassword')->getData()
                ));

            $participant->setActif(true);
            $participant->setRoles(["ROLE_USER"]);
            $participant->setPseudo($participant->getNom() . "." . $participant->getPrenom());

            $participantRepository->add($participant, true);

            $this->addFlash('success', 'Participant ajouté');

            return $this->redirectToRoute('participant_new');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $registrationForm->createView(),
        ]);
    }

    #[Route('/profil', name: 'profil', methods: ['GET'])]
    public function profil(): Response
    {
        if (!$this->getUser())
            throw new AccessDeniedException("Vous devez etre connecter!");


        return $this->render('participant/show.html.twig', [
            'participant' => $this->getUser(),
        ]);
    }

    #[Route('/upload', name: 'upload')]
    function upload(AjoutParticipant $ajoutParticipant, Request $request): Response
    {
        $csvForm = $this->createForm(CSVType::class);
        $csvForm->handleRequest($request);

        if ($csvForm->isSubmitted() && $csvForm->isValid()) {
            /**
             * @var UploadedFile $file
             */
            $file = $csvForm->get('csvFile')->getData();

            $ajoutParticipant->ajouterParticipantCSV($file);

        }
        $this->addFlash('success', 'Participants ajoutés');
        return $this->render('participant/upload.html.twig', [
            'csvForm' => $csvForm->createView()
        ]);
    }

    #[Route('/participant/{id}', name: 'participant', requirements: ["id" => "\d+"])]
    public function participant(ParticipantRepository $participantRepository, int $id): Response
    {
        $participant = $participantRepository->find($id);

        if (!$participant) {
            throw $this->createNotFoundException("Oups, ce participant n'existe pas");
        }

        return $this->render('participant/participant.html.twig', [
            'participant' => $participant,
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
            $plainPassword = $form->get('actualPassword')->getData();
            if ($plainPassword != null) {
                if (!$userPasswordHasher->isPasswordValid($participant, $plainPassword)) {
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
            }


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
