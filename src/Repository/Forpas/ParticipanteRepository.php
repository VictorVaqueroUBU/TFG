<?php

namespace App\Repository\Forpas;

use App\Entity\Forpas\Participante;
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
     * @param EdicionRepository $edicion La edición actual para la cual se buscan participantes disponibles.
     * @return Participante[] Retorna un arreglo de objetos `Participante` que cumplen con los criterios.
     */
    public function findPossibleEntries($edicion)
    {
        // Obtener el ID del curso de la edición actual
        $cursoId = $edicion->getCurso()->getId();

        // Crear el QueryBuilder principal
        $qb = $this->createQueryBuilder('p');

        // Crear una subconsulta para filtrar participantes inscritos en el curso actual
        $subQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from('App\Entity\Forpas\ParticipanteEdicion', 'pe')
            ->join('pe.edicion', 'e')
            ->where('pe.participante = p.id')
            ->andWhere('e.curso = :cursoId');

        // Construir la consulta principal usando el QueryBuilder
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
