<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\FiltreTexteType;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ville', name: 'ville_')]
class VilleController extends AbstractController
{
    #[Route('/manage', name: 'manage')]
    public function manage(Request $request, VilleRepository $villeRepository): Response
    {


        $ville = new Ville();
        $villeForm = $this->createForm(VilleType::class, $ville);

        $villeForm->handleRequest($request);

        if ($villeForm->isSubmitted() && $villeForm->isValid()) {
            $villeRepository->add($ville, true);
            $this->addFlash('success', 'Ville créée');

            $this->redirectToRoute('ville_manage');
        }

        $filterForm = $this->createForm(FiltreTexteType::class, null, ['csrf_protection' => false]);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted()) {
            $filter = $filterForm->getData();

            $villes = $villeRepository->findVillesByTextSearch($filter);
        } else {
            $villes = $villeRepository->findAllOrderedByName();
        }

        return $this->render('ville/manage.html.twig', [
            'villeForm' => $villeForm->createView(),
            'villes' => $villes,
            'filterForm' => $filterForm->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ["id" => "\d+"])]
    public function delete(Request $request, VilleRepository $villeRepository, int $id): Response
    {
        $ville = $villeRepository->find($id);

        if (!$ville) {
            throw $this->createNotFoundException("Oups, cette ville n'existe pas");
        }

        $villeRepository->remove($ville, true);

        $this->addFlash('success', 'Ville supprimée');

        return $this->redirectToRoute('ville_manage');
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ["id" => "\d+"])]
    public function edit(Request $request, VilleRepository $villeRepository, int $id): Response
    {
        $ville = $villeRepository->find($id);

        $villeForm = $this->createForm(VilleType::class,$ville);
        $villeForm->handleRequest($request);

        if (!$ville) {
            throw $this->createNotFoundException("Oups, cette ville n'existe pas");
        }

        if($villeForm->isSubmitted()){

            $villeRepository->add($ville, true);

            $this->addFlash('success', 'Ville modifiée');

            return $this->redirectToRoute('ville_manage');
        }

        return $this->render('ville/edit.html.twig', [
            'villeForm' => $villeForm->createView(),
            'ville' => $ville,
        ]);
    }

}
