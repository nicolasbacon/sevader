<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\AnnulerSortieType;
use App\Form\FiltreType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/sortie', name: 'sortie_')]
class SortieController extends AbstractController
{

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

    #[Route('/published/{id}', name: 'published', requirements: ["id" => "\d+"])]
    public function published(EtatRepository $etatRepository, SortieRepository $sortieRepository, int $id): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Oups, cette sortie n'existe pas");
        }
        $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));

        $sortieRepository->add($sortie, true);

        return $this->redirectToRoute('main_home');
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ["id" => "\d+"])]
    public function delete(EtatRepository $etatRepository, SortieRepository $sortieRepository, int $id): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Oups, cette sortie n'existe pas");
        }

        $sortieRepository->remove($sortie, true);

        $this->addFlash('success', 'Sortie supprimée');

        return $this->redirectToRoute('main_home');
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ["id" => "\d+"])]
    public function edit(Request $request, EtatRepository $etatRepository, SortieRepository $sortieRepository, int $id): Response
    {
        $sortie = $sortieRepository->findOneWithRelations($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Oups, cette sortie n'existe pas");
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->get('ville')->setData($sortie->getLieu()->getVille());


        $sortieForm->add('lieu', EntityType::class, [
            'class' => Lieu::class,
            'choices' => $sortie->getLieu()->getVille()->getLieux()->toArray(),
        ]);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            //mettre l'état de la sortie créée à créée ou ouverte en fonction du submit utilisé
            if ($sortieForm->get('enregistrer')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Créée']));
            } elseif ($sortieForm->get('publier')->isClicked()) {
                $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Ouverte']));
            }


            $sortieRepository->add($sortie, true);

            //ajouter les messages flash en fonction du submit cliqué
            if ($sortieForm->get('enregistrer')->isClicked()) {
                $this->addFlash('success', 'Sortie créée');
            } elseif ($sortieForm->get('publier')->isClicked()) {
                $this->addFlash('success', 'Sortie publiée');
            }
            return $this->redirectToRoute('sortie_sortie', [
                'id' => $sortie->getId(),
            ]);

        }
        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'sortieForm' => $sortieForm->createView()
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

        if ($annulerSortieForm->isSubmitted() && $annulerSortieForm->isValid()) {
            $sortie->setEtat($etatRepository->findOneBy(['libelle' => 'Annulée']));

            $sortieRepository->add($sortie, true);

            $this->addFlash('success', 'Sortie annulée');

            return $this->redirectToRoute('main_home');
        }


        return $this->render('sortie/cancel.html.twig', [
            'sortie' => $sortie,
            'annulerSortieForm' => $annulerSortieForm->createView()
        ]);

    }
}
