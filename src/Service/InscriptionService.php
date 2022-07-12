<?php

namespace App\Service;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use function PHPUnit\Framework\never;

class InscriptionService
{
    private $etatCloture;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->etatCloture = $entityManager->getRepository(Etat::class)->findOneByLibelle('Cloturée');
    }

    public function inscrireParticipant(Sortie $sortie, Participant $participant, EntityManagerInterface $entityManager)
    {

        // Si la date de cloture est passer
        if ($sortie->getDateLimiteInscription() <= new \DateTime()) {
            $sortie->setEtat($this->etatCloture);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        if ($sortie->getEtat()->getLibelle() === "Ouverte") {
            if (count($sortie->getParticipants()) < $sortie->getNbInscriptionMax()) {
                $sortie->addParticipant($participant);
                if ($sortie->getParticipants()->count() === $sortie->getNbInscriptionMax())
                    $sortie->setEtat($this->etatCloture);
                $entityManager->persist($sortie);
                $entityManager->flush();
            } else throw new AccessDeniedException("Le nombre de participants maximum est deja atteint");
        } else throw new AccessDeniedException("Les inscriptions à cette sortie sont terminées");

        return $sortie->getParticipants();
    }

    public function desinscrireParticipant(Sortie $sortie, Participant $participant, EntityManagerInterface $entityManager)
    {
        // Si la date de cloture est passer
        if ($sortie->getDateLimiteInscription() <= new \DateTime()) {
            $sortie->setEtat($this->etatCloture);
            $entityManager->persist($sortie);
            $entityManager->flush();
            throw new AccessDeniedException("Les inscriptions à cette sortie sont terminées");
        } else {
            $sortie->removeParticipant($participant);
            if ($sortie->getParticipants()->count() < $sortie->getNbInscriptionMax())
                $sortie->setEtat($entityManager->getRepository(Etat::class)->findOneByLibelle('Ouverte'));
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $sortie->getParticipants();
    }

}