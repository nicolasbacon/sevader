<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var EtatRepository
     */
    private $etatRepository;
    /**
     * @var CampusRepository
     */
    private $campusRepository;

    public function __construct(ManagerRegistry $registry, Security $security, EtatRepository $etatRepository, CampusRepository $campusRepository)
    {
        parent::__construct($registry, Sortie::class);
        $this->security = $security;
        $this->etatRepository = $etatRepository;
        $this->campusRepository = $campusRepository;
    }

    public function add(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneWithRelations(int $id): Sortie
    {
        $qb = $this->createQueryBuilder('s');
        $qb->leftJoin('s.campus', 'c')
            ->addSelect('c')
            ->leftJoin('s.lieu', 'l')
            ->addSelect('l')
            ->leftJoin('l.ville','v')
            ->addSelect('v')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllOrderedBySites(): array
    {

        $qb = $this->createQueryBuilder('s');
        $qb->leftJoin('s.etat','e')
            ->addSelect('e')
            ->leftJoin('s.organisateur','o')
            ->addSelect('o')
            ->leftJoin('s.participants','p')
            ->addSelect('p')
            ->andWhere("(s.etat = :etat1 AND s.organisateur = :organizer)")
            ->setParameter('etat1', $this->etatRepository->findOneBy(['libelle' => 'Créée']))
            ->setParameter('organizer', $this->security->getUser())
            ->orWhere('s.etat not in (:etat2)')
            ->setParameter('etat2', $this->etatRepository->findOneBy(['libelle' => ['Archivée', 'Créée']]))
            ->andWhere('s.campus IN (:campus)');
        if ($this->security->getUser()) {
            $qb->setParameter('campus', $this->security->getUser()->getCampus());
        } else {
            $qb->setParameter('campus', $this->campusRepository->findAll());
        }
        return $qb->getQuery()->getResult();
    }

    public function findFiltered(mixed $filters)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->leftJoin('s.etat','e')
            ->addSelect('e')
            ->leftJoin('s.organisateur','o')
            ->addSelect('o')
            ->leftJoin('s.participants','p')
            ->addSelect('p')
            ->andWhere("(s.etat = :etat1 AND s.organisateur = :organizer)")
            ->setParameter('etat1', $this->etatRepository->findOneBy(['libelle' => 'Créée']))
            ->setParameter('organizer', $this->security->getUser())
            ->orWhere('s.etat not in (:etat2)')
            ->setParameter('etat2', $this->etatRepository->findOneBy(['libelle' => ['Archivée', 'Créée']]));
        if ($filters['site'] != null) {
            $qb->andWhere('s.campus = :campus')
                ->setParameter('campus', $filters['site']);
        }
        if ($filters['textSearch'] != null) {
            $qb->andWhere('s.nom LIKE LOWER(:nom)')
                ->setParameter('nom', "%{$filters['textSearch']}%");
        }
        if ($filters['startDate'] != null) {
            $qb->andWhere('s.dateHeureDebut >= :startDate')
                ->setParameter('startDate', $filters['startDate']);
        }
        if ($filters['endDate'] != null) {
            $qb->andWhere('s.dateHeureDebut <= :endDate')
                ->setParameter('endDate', $filters['endDate']);
        }
        if ($filters['organizer']) {
            $qb->andWhere('s.organisateur = :organizer')
                ->setParameter('organizer', $this->security->getUser());
        }
        if ($filters['subscription'] == 'registered') {
            $qb->andWhere(':registered MEMBER OF s.participants')
                ->setParameter('registered', $this->security->getUser());
        }
        if ($filters['subscription'] == 'unregistered') {
            $qb->andWhere(':unregistered NOT MEMBER OF s.participants')
                ->setParameter('unregistered', $this->security->getUser());
        }
        if ($filters['ended']) {
            $qb->andWhere('s.etat = :ended')
                ->setParameter('ended', $this->etatRepository->findOneBy(['libelle' => 'Passée']));
        }


        return $qb->getQuery()->getResult();
    }

    public function findOneWithParticipants(int $id): Sortie
    {
        return $this->createQueryBuilder('s')
            ->join('s.participants', 'p')
            ->addSelect('p')
            ->andWhere('s.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


}
