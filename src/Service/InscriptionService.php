<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use function PHPUnit\Framework\never;

class InscriptionService
{
    public function inscrireParticipant(Sortie $sortie, Participant $participant, EntityManagerInterface $entityManager)
    {
        if ($sortie->getEtat()->getLibelle() === "Ouverte") {
            if (count($sortie->getParticipants()) < $sortie->getNbInscriptionMax()) {
                $sortie->addParticipant($participant);
                $entityManager->persist($sortie);
                $entityManager->flush();
            } else throw new AccessDeniedException("Le nombre de participants maximum est deja atteint");
        } else throw new AccessDeniedException("Les inscriptions à cette sortie sont terminées");

        return $sortie->getParticipants();
    }

    public function desinscrireParticipant(Sortie $sortie, Participant $participant, EntityManagerInterface $entityManager)
    {
        $sortie->removeParticipant($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();
    }

}