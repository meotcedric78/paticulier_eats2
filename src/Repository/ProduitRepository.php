<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function findByCpCategorie($cp, $categorie)
    {
        return $this->createQueryBuilder('a')
            ->andWhere("a.cp = $cp ", "a.categorie = $categorie")
            ->getQuery()
            ->getResult()
            ;
    }
    public function findByCpSousCategorie($cp, $souscategorie)
    {
        return $this->createQueryBuilder('a')
            ->andWhere("a.cp = $cp", "a.souscategorie = $souscategorie")
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByCategorieSousCategorie($categorie, $souscategorie)
    {
        return $this->createQueryBuilder('a')
            ->andWhere("a.categorie = $categorie", "a.souscategorie = $souscategorie")
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByCategorieSousCategorieCp($categorie, $souscategorie,$cp)
    {
        return $this->createQueryBuilder('a')
            ->andWhere("a.categorie = $categorie", "a.souscategorie = $souscategorie", "a.cp = $cp")
            ->getQuery()
            ->getResult()
            ;
    }



    // /**
    //  * @return Produit[] Returns an array of Produit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Produit
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
