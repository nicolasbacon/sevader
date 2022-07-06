<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;

class InscriptionService
{
    public function inscrireParticipant(Sortie $sortie, Participant $participant, EntityManagerInterface $entityManager) {
        $sortie->addParticipant($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();
    }

    public function desinscrireParticipant(Sortie $sortie, Participant $participant, EntityManagerInterface $entityManager) {
        $sortie->removeParticipant($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();
    }

}