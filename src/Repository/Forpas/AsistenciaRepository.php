<?php

namespace App\Repository\Forpas;

use App\Entity\Forpas\Asistencia;
use App\Entity\Forpas\Edicion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Asistencia>
 */
class AsistenciaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asistencia::class);
    }

    /**
     * Método para contar las asistencias confirmadas para cada sesión de una edición.
     *
     * @param Edicion $edicion La edición para la cual se cuentan las asistencias.
     * @return array<string|int, int> Un array asociativo con la estructura [sesionId => totalAsistencias].
     */
    public function contarAsistenciasPorSesion(Edicion $edicion): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('IDENTITY(a.sesion) as sesionId, COUNT(a.id) as totalAsistencias')
            ->where('a.sesion IN (:sesiones)')
            ->andWhere('a.asiste = true')
            ->setParameter('sesiones', $edicion->getSesionesEdicion()->toArray())
            ->groupBy('a.sesion');

        $result = $qb->getQuery()->getResult();

        // Convertimos el resultado en un array asociativo [sesionId => totalAsistencias]
        return array_column($result, 'totalAsistencias', 'sesionId');
    }

    /**
     * Método para contar las justificaciones confirmadas para cada sesión de una edición.
     *
     * @param Edicion $edicion La edición para la cual se cuentan las justificaciones.
     * @return array<string|int, int> Un array asociativo con la estructura [sesionId => totalJustificaciones].
     */
    public function contarJustificacionesPorSesion(Edicion $edicion): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('IDENTITY(a.sesion) as sesionId, COUNT(a.id) as totalJustificaciones')
            ->where('a.sesion IN (:sesiones)')
            ->andWhere('a.justifica = true')
            ->setParameter('sesiones', $edicion->getSesionesEdicion()->toArray())
            ->groupBy('a.sesion');

        $result = $qb->getQuery()->getResult();

        // Convertimos el resultado en un array asociativo [sesionId => totalJustificaciones]
        return array_column($result, 'totalJustificaciones', 'sesionId');
    }

    /**
     * Método para obtener todas las asistencias de una edición específica.
     *
     * @param int $edicionId El ID de la edición para la cual se buscan las asistencias.
     * @return Asistencia[] Un array de objetos `Asistencia` correspondientes a la edición especificada.
     */
    public function findAllByEdicion(int $edicionId): array
    {
        return $this->createQueryBuilder('a')
            ->join('a.sesion', 's')
            ->join('s.edicion', 'e')
            ->join('a.participante', 'p')
            ->where('e.id = :edicionId')
            ->setParameter('edicionId', $edicionId)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Asistencia[] Returns an array of Asistencia objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Asistencia
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
