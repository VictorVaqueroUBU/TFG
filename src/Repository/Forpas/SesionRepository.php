<?php

namespace App\Repository\Forpas;

use App\Entity\Forpas\Sesion;
use App\Entity\Forpas\Edicion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sesion>
 */
class SesionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sesion::class);
    }

    /**
     * Método para calcular el número de sesiones y las horas totales de una edición específica.
     *
     * @param Edicion $edicion La edición sobre la que se calculan las sesiones y las horas.
     * @return array Un array asociativo con las siguientes claves:
     *               - `sesionesGrabadas`: Número total de sesiones registradas.
     *               - `horasGrabadas`: Suma total de las horas (duración) de todas las sesiones.
     *               - `horasVirtualesGrabadas`: Suma total de las horas de las sesiones virtuales (tipo = 1).
     */
    public function calcularSesionesYHoras(Edicion $edicion): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) as sesionesGrabadas, SUM(s.duracion) as horasGrabadas, SUM(CASE WHEN s.tipo = 1 THEN s.duracion ELSE 0 END) as horasVirtualesGrabadas')
            ->where('s.edicion = :edicion')
            ->setParameter('edicion', $edicion);

        return $qb->getQuery()->getSingleResult();
    }

    //    /**
    //     * @return Sesion[] Returns an array of Sesion objects
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

    //    public function findOneBySomeField($value): ?Sesion
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
