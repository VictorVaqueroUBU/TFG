<?php

namespace App\Repository\Forpas;

use App\Entity\Forpas\Participante;
use App\Entity\Forpas\ParticipanteEdicion;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParticipanteEdicion>
 */
class ParticipanteEdicionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParticipanteEdicion::class);
    }

    /**
     * Método para encontrar las próximas ediciones asociadas a un participante.
     *
     * @param Participante $participante participante sobre el que se buscan las próximas ediciones.
     * @return ParticipanteEdicion[] array de objetos `ParticipanteEdicion` que cumplen con los criterios.
     */
    public function findProximasEdiciones(Participante $participante): array
    {
        return $this->createQueryBuilder('pe')
            ->join('pe.edicion', 'e')
            ->where('pe.participante = :participante')
            ->andWhere('e.fecha_inicio > :now')
            ->setParameter('participante', $participante)
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->getResult();
    }

    /**
     * Método para encontrar las ediciones certificadas asociadas a un participante.
     *
     * @param Participante $participante participante sobre el que se buscan las ediciones certificadas.
     * @return ParticipanteEdicion[] array de objetos `ParticipanteEdicion` que cumplen con los criterios.
     */
    public function findEdicionesCertificadas(Participante $participante): array
    {
        return $this->createQueryBuilder('pe')
            ->join('pe.edicion', 'e')
            ->where('pe.participante = :participante')
            ->andWhere('pe.certificado = true')
            ->setParameter('participante', $participante)
            ->getQuery()
            ->getResult();
    }

    /**
     * Método para encontrar las ediciones no certificadas de un participante.
     *
     * @param Participante $participante participante sobre el que se buscan las ediciones no certificadas.
     * @return ParticipanteEdicion[] array de objetos `ParticipanteEdicion` que cumplen con los criterios.
     */
    public function findOtrasEdiciones(Participante $participante): array
    {
        return $this->createQueryBuilder('pe')
            ->join('pe.edicion', 'e')
            ->where('pe.participante = :participante')
            ->andWhere('pe.certificado IS NULL OR pe.certificado != true')
            ->andWhere('e.fecha_inicio < :now')
            ->setParameter('participante', $participante)
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return ParticipanteEdicion[] Returns an array of ParticipanteEdicion objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ParticipanteEdicion
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
