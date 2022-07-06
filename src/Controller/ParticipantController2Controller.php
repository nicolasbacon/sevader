<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/participant/controller2')]
class ParticipantController2Controller extends AbstractController
{
    #[Route('/', name: 'app_participant_controller2_index', methods: ['GET'])]
    public function index(ParticipantRepository $participantRepository): Response
    {
        return $this->render('participant_controller2/index.html.twig', [
            'participants' => $participantRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_participant_controller2_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ParticipantRepository $participantRepository): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participantRepository->add($participant, true);

            return $this->redirectToRoute('app_participant_controller2_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participant_controller2/new.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_controller2_show', methods: ['GET'])]
    public function show(Participant $participant): Response
    {
        return $this->render('participant_controller2/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participant_controller2_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participant $participant, ParticipantRepository $participantRepository): Response
    {
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participantRepository->add($participant, true);

            return $this->redirectToRoute('app_participant_controller2_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participant_controller2/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_controller2_delete', methods: ['POST'])]
    public function delete(Request $request, Participant $participant, ParticipantRepository $participantRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->request->get('_token'))) {
            $participantRepository->remove($participant, true);
        }

        return $this->redirectToRoute('app_participant_controller2_index', [], Response::HTTP_SEE_OTHER);
    }
}
