<?php

namespace App\Controller\Api;

use App\Repository\LieuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/lieu', name: 'api_lieu_')]
class LieuController extends AbstractController
{
    #[Route('/retrieve', name: 'retrieve', methods: 'POST')]
    public function retrieveOne(Request $request, LieuRepository $lieuRepository): Response
    {
        $json = json_decode($request->getContent());
        //on récupère le lieu correspondant à l'id
        $lieu = $lieuRepository->find($json->lieu_id);

        // on transforme la série en json et on la renvoie
        return $this->json([
            "rue"=> $lieu->getRue(),
            "codePostal"=> $lieu->getVille()->getCodePostal(),
            "latitude" => $lieu->getLatitude(),
            "longitude" => $lieu->getLongitude()
            ]);
    }
}
