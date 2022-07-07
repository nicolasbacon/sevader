<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;

class InscriptionService
{
    public function inscrireParticipant(Sortie $sortie, Participant $participant, EntityManagerInterface $entityManager)
    {
        if ($sortie->getEtat()->getLibelle() === "Ouverte") {
            if (count($sortie->getParticipants()) < $sortie->getNbInscriptionMax()) {
                $sortie->addParticipant($participant);
                $entityManager->persist($sortie);
                $entityManager->flush();
            } else return "Le nombre de participants maximum est deja atteint";
        } else return "Les inscriptions à cette sortie sont terminées";
        return null;
    }

    public function desinscrireParticipant(Sortie $sortie, Participant $participant, EntityManagerInterface $entityManager)
    {
        $sortie->removeParticipant($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();
    }

}