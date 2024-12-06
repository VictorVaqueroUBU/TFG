<?php

namespace App\Repository\Forpas;

use App\Entity\Forpas\FormadorEdicion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormadorEdicion>
 */
class FormadorEdicionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormadorEdicion::class);
    }

    /**
     * Obtiene las ediciones abiertas (estado == 0) asignadas a un formador específico.
     *
     * @param int $formadorId ID del formador.
     * @return FormadorEdicion[] Lista de FormadorEdicion con las ediciones abiertas.
     */
    public function findEdicionesAbiertasByFormador(int $formadorId): array
    {
        return $this->createQueryBuilder('fe')
            ->join('fe.edicion', 'e')
            ->where('e.estado = :estado')
            ->andWhere('fe.formador = :formadorId')
            ->setParameter('estado', 0)
            ->setParameter('formadorId', $formadorId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtiene las ediciones cerradas (estado != 0) asignadas a un formador específico.
     *
     * @param int $formadorId ID del formador.
     * @return FormadorEdicion[] Lista de FormadorEdicion con las ediciones cerradas.
     */
    public function findEdicionesCerradasByFormador(int $formadorId): array
    {
        return $this->createQueryBuilder('fe')
            ->join('fe.edicion', 'e')
            ->where('e.estado != :estado')
            ->andWhere('fe.formador = :formadorId')
            ->setParameter('estado', 0)
            ->setParameter('formadorId', $formadorId)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return FormadorEdicion[] Returns an array of FormadorEdicion objects
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

    //    public function findOneBySomeField($value): ?FormadorEdicion
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
