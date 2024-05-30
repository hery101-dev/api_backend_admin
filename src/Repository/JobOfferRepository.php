<?php

namespace App\Repository;

use App\Entity\JobOffer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobOffer>
 *
 * @method JobOffer|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobOffer|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobOffer[]    findAll()
 * @method JobOffer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobOfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobOffer::class);
    }


    public function findByKeyword($keyword)
    {
        // Supposez que vous êtes dans une méthode d'un repository JobOfferRepository

        // $data = $this->createQueryBuilder('j')
        //     ->innerJoin('j.category', 'c')  // 'category' est le nom de la propriété relationnelle dans JobOffer
        //     ->innerJoin('j.company', 'co')  // 'company' est le nom de la propriété relationnelle dans JobOffer
        //     ->innerJoin('j.location', 'l')  // 'location' est le nom de la propriété relationnelle dans JobOffer
        //     ->where('j.title LIKE :title')
        //     // ->andWhere('c.category_name LIKE :categoryName')  // 'name' est le champ dans l'entité Category que vous voulez rechercher
        //     // ->andWhere('co.company_name LIKE :companyName')  // 'company_name' est le champ dans l'entité Company que vous voulez rechercher
        //     // ->andWhere('l.city LIKE :city')  // 'city' est le champ dans l'entité Location que vous voulez rechercher
        //     ->setParameters([
        //         'title' => '%'.$keyword.'%',

        //     ])
        //     ->addOrderBy('j.createdAt', 'DESC');

        // return $data->getQuery()->getResult();



        $data = $this->createQueryBuilder('j')
            ->where('j.title LIKE :title')
            ->setParameter('title', '%' . $keyword . '%') 
            ->addOrderBy('j.createdAt', 'DESC');

        return $data->getQuery()->getResult();
    }

    //    /**
    //     * @return JobOffer[] Returns an array of JobOffer objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('j.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?JobOffer
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
