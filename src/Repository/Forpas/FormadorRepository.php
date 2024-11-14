<?php

namespace App\Repository\Forpas;

use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Formador;
use App\Entity\Forpas\FormadorEdicion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Formador>
 */
class FormadorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formador::class);
    }

    /**
     * Método para buscar todos los formadores que no están asignados a la edición especificada.
     *
     * @param Edicion $edicion La edición actual para la cual se buscan formadores disponibles.
     * @return Formador[] Un array de objetos `Formador` que cumplen con el criterio de búsqueda.
     */
    public function findPossibleTeacher(Edicion $edicion): array
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.formadorEdiciones', 'fe')
            ->andWhere('fe.edicion IS NULL OR fe.edicion != :edicion')
            ->andWhere('f.id NOT IN (
            SELECT IDENTITY(fe2.formador) 
            FROM ' . FormadorEdicion::class . ' fe2 
            WHERE fe2.edicion = :edicion
        )')
            ->setParameter('edicion', $edicion)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Formador[] Returns an array of Formador objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Formador
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
