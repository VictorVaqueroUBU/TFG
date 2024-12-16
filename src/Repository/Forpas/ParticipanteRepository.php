<?php

namespace App\Repository\Forpas;

use App\Entity\Forpas\Edicion;
use App\Entity\Forpas\Participante;
use App\Entity\Forpas\ParticipanteEdicion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participante>
 */
class ParticipanteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participante::class);
    }

    /**
     * Este método busca todos los participantes que tienen una unidad asignada (`unidad IS NOT NULL`)
     * y que no están inscritos en ninguna edición del curso al que pertenece la edición especificada.
     *
     * @param Edicion $edicion La edición actual para la cual se buscan participantes disponibles.
     * @return Participante[] Retorna un arreglo de objetos `Participante` que cumplen con los criterios.
     */
    public function findPossibleEntries(Edicion $edicion): array
    {
        $cursoId = $edicion->getCurso()->getId();
        $qb = $this->createQueryBuilder('p');
        $subQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ParticipanteEdicion::class, 'pe')
            ->join('pe.edicion', 'e')
            ->where('pe.participante = p.id')
            ->andWhere('e.curso = :cursoId');

        return $qb
            ->where('p.unidad IS NOT NULL')
            ->andWhere(
                $qb->expr()->not($qb->expr()->exists($subQuery->getDQL()))
            )
            ->setParameter('cursoId', $cursoId)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Participante[] Returns an array of Participante objects
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

    //    public function findOneBySomeField($value): ?Participante
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
