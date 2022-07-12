<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Form\FiltreTexteType;
use App\Repository\CampusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/campus', name: 'campus_')]
class CampusController extends AbstractController
{
    #[Route('/manage', name: 'manage')]
    public function manage(Request $request, CampusRepository $campusRepository): Response
    {
        $filterForm = $this->createForm(FiltreTexteType::class, null, ['csrf_protection' => false]);
        $filterForm->handleRequest($request);


        $campus = new Campus();
        $campusForm = $this->createForm(CampusType::class, $campus);

        $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            $campus->setNom(strtoupper($campus->getNom()));
            $campusRepository->add($campus, true);
            $this->addFlash('success', 'Campus créé');

            $this->redirectToRoute('campus_manage');
        }


        if ($filterForm->isSubmitted()) {
            $filter = $filterForm->getData();

            $listeCampus = $campusRepository->findCampusByTextSearch($filter);
        } else {
            $listeCampus = $campusRepository->findAllOrderedByName();
        }

        return $this->render('campus/manage.html.twig', [
            'campusForm' => $campusForm->createView(),
            'listeCampus' => $listeCampus,
            'filterForm' => $filterForm->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ["id" => "\d+"])]
    public function delete(Request $request, CampusRepository $campusRepository, int $id): Response
    {
        $campus = $campusRepository->find($id);

        if (!$campus) {
            throw $this->createNotFoundException("Oups, ce campus n'existe pas");
        }

        $campusRepository->remove($campus, true);

        $this->addFlash('success', 'Campus supprimé');

        return $this->redirectToRoute('campus_manage');
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ["id" => "\d+"])]
    public function edit(Request $request, CampusRepository $campusRepository, int $id): Response
    {
        $campus = $campusRepository->find($id);

        $campusForm = $this->createForm(CampusType::class, $campus);
        $campusForm->handleRequest($request);

        if (!$campus) {
            throw $this->createNotFoundException("Oups, ce campus n'existe pas");
        }

        if ($campusForm->isSubmitted()) {

            $campusRepository->add($campus, true);

            $this->addFlash('success', 'Campus modifié');

            return $this->redirectToRoute('campus_manage');
        }

        return $this->render('campus/edit.html.twig', [
            'campusForm' => $campusForm->createView(),
            'campus' => $campus,
        ]);
    }


}
