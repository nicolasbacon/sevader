<?php

namespace App\Controller;

use App\Form\FiltreType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    #[Route('/', name: 'main_home')]
    public function index(EtatRepository $etatRepository, SortieRepository $sortieRepository, Request $request): Response
    {
        $filterForm = $this->createForm(FiltreType::class, null, ['csrf_protection' => false]);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() ) {
            $filters = $filterForm->getData();

            $sorties = $sortieRepository->findFiltered( $filters);
        } else {
            $sorties = $sortieRepository->findAllOrderedBySites();
        }


        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'filterForm' => $filterForm->createView()
        ]);
    }







}
