<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\AnnulerSortieType;
use App\Form\FiltreType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{

    #[Route('/list', name: 'list')]
    public function list(EtatRepository $etatRepository, SortieRepository $sortieRepository, Request $request): Response
    {
        $filterForm = $this->createForm(FiltreType::class, null, ['csrf_protection' => false]);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted()) {
            $filters = $filterForm->getData();

            $sorties = $sortieRepository->findFiltered($etatRepository, $filters);
        } else {
            $sorties = $sortieRepository->findAllOrderedBySites();
        }


        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'filterForm' => $filterForm->createView()
        ]);
    }

    #[Route('/sortie/{id}', name: 'sortie', requirements: ["id" => "\d+"])]
    public function sortie(SortieRepository $sortieRepository, int $id): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Oups, cette sortie n'existe pas");
        }

        return $this->render('sortie/sortie.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, SortieRepository $sortieRepository, EtatRepository $etatRepository, ParticipantRepository $participantRepository): Response
    {

        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            //mettre l'état de la sortie créée à créée ou ouverte en fonction du submit utilisé
            if ($sortieForm->get('enregistrer')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Créée']));
            } elseif ($sortieForm->get('publier')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
            }

            //ajouter l'organisateur aux participants s'il le souhaite
            if ($sortieForm->get('inscriptionAuto')->getData()) {
                $sortie->addParticipant($participantRepository->find($this->getUser()->getId()));
            }

            //setter le user connecté en tant qu'organisateur et setter le campus du user connecté en tant
            //que campus de la sortie
            $sortie->setOrganisateur($participantRepository->find($this->getUser()->getId()));
            $sortie->setCampus($this->getUser()->getCampus());

            $sortieRepository->add($sortie, true);

            //ajouter les messages flash en fonction du submit cliqué
            if ($sortieForm->get('enregistrer')->isClicked()) {
                $this->addFlash('success', 'Sortie créée');
            } elseif ($sortieForm->get('publier')->isClicked()) {
                $this->addFlash('success', 'Sortie publiée');
            }
            return $this->redirectToRoute('sortie_sortie', [
                'id' => $sortie->getId()
            ]);
        }


        return $this->render('sortie/new.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    #[Route('/cancel/{id}', name: 'cancel', requirements: ["id" => "\d+"])]
    public function cancel(Request $request, EtatRepository $etatRepository, SortieRepository $sortieRepository, int $id): Response
    {
        $sortie = $sortieRepository->find($id);
        $annulerSortieForm = $this->createForm(AnnulerSortieType::class, $sortie);

        $annulerSortieForm->handleRequest($request);


        if (!$sortie) {
            throw $this->createNotFoundException("Oups, cette sortie n'existe pas");
        }

        if ($annulerSortieForm->isSubmitted()) {
            $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Annulée']));

        }

        $sortieRepository->add($sortie, true);

        return $this->redirectToRoute('sortie_list', [
            'sortie' => $sortie,
            'annulerSortieForm' => $annulerSortieForm->createView()
        ]);

    }
}
