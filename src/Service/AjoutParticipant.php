<?php

namespace App\Service;

use App\Entity\Participant;
use App\Form\CSVType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AjoutParticipant
{
    /**
     * @var UserPasswordHasherInterface $userPasswordHasher
     */
    private $userPasswordHasher;
    /**
     * @var CampusRepository $campusRepository
     */
    private $campusRepository;
    /**
     * @var ParticipantRepository $participantRepository
     */
    private $participantRepository;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, CampusRepository $campusRepository, ParticipantRepository $participantRepository)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->campusRepository = $campusRepository;
        $this->participantRepository = $participantRepository;
    }


    public function ajouterParticipantCSV(UploadedFile $file)
    {
        // Open the file
        if (($handle = fopen($file->getPathname(), "r")) !== false) {
            // Read and process the lines.
            // Skip the first line if the file includes a header
            fgetcsv($handle,null,';');
            while (($data = fgetcsv($handle,null,';')) !== false) {
                // Do the processing: Map line to entity, validate if needed
                $participant = new Participant();
                // Assign fields
                $participant->setEmail($data[0]);
                $participant->setNom($data[1]);
                $participant->setPrenom($data[2]);
                $participant->setTelephone($data[3]);
                $participant->setCampus($this->campusRepository->findOneBy(['nom' => strtoupper($data[4])]));
                $participant->setRoles(['ROLE_USER']);
                $participant->setActif(true);
                $participant->setPseudo($participant->getPrenom() . '.' . $participant->getNom());
                $participant->setPassword($this->userPasswordHasher->hashPassword(
                    $participant,
                    'azerty'
                ));
                $this->participantRepository->add($participant, true);
            }
            fclose($handle);

        }
    }

}