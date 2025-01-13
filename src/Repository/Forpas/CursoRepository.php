<?php

namespace App\Repository\Forpas;

use App\Entity\Forpas\Curso;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Curso>
 */
class CursoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Curso::class);
    }

    /**
     * Método para encontrar todos los cursos cuyo código corresponde al año especificado.
     *
     * @param int $year El año para el cual se buscan los cursos (e.g., 2024).
     * @return Curso[] Un array de objetos `Curso` que cumplen con el criterio de búsqueda.
     */
    public function findByYear(int $year): array
    {
        $yearCode = substr((string)$year, -2);

        return $this->createQueryBuilder('c')
            ->andWhere('c.codigo_curso LIKE :yearCode')
            ->andWhere('LENGTH(c.codigo_curso) = 5')
            ->setParameter('yearCode', $yearCode . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Método para encontrar todos los cursos cuyo código corresponde al año especificado
     * y que tienen el campo "visible" en true.
     *
     * @param int $year El año para el cual se buscan los cursos (e.g., 2024).
     * @return Curso[] Un array de objetos `Curso` que cumplen con el criterio de búsqueda.
     */
    public function findByYearVisibles(int $year): array
    {
        $yearCode = substr((string)$year, -2);

        return $this->createQueryBuilder('c')
            ->andWhere('c.codigo_curso LIKE :yearCode')
            ->andWhere('LENGTH(c.codigo_curso) = 5')
            ->andWhere('c.visible_web = :visible')
            ->setParameter('yearCode', $yearCode . '%')
            ->setParameter('visible', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra el primer código de curso libre en función del año.
     *
     * @param int $year Año para el cual se desea obtener el primer código de curso libre (por ejemplo, 2024).
     * @return string|null Retorna el primer código libre en el formato "aaXXX" o null si no hay códigos disponibles.
     */
    public function findPrimerCodigoCursoLibre(int $year): ?string
    {
        $prefix = substr((string)$year, -2);

        $result = $this->createQueryBuilder('c')
            ->select('c.codigo_curso')
            ->where('c.codigo_curso LIKE :prefix')
            ->setParameter('prefix', $prefix . '%')
            ->orderBy('c.codigo_curso', 'ASC')
            ->getQuery()
            ->getResult();

        $cursosExistentes = array_map(function($item) use ($prefix) {
            return (int) substr($item['codigo_curso'], strlen($prefix));
        }, $result);

        $siguienteNumeroLibre = 1;
        foreach ($cursosExistentes as $numero) {
            if ($numero !== $siguienteNumeroLibre) {
                break;
            }
            $siguienteNumeroLibre++;
        }

        // Formateamos el número encontrado en el formato "aaXXX" y lo retornamos
        return sprintf('%s%03d', $prefix, $siguienteNumeroLibre);
    }


    //    /**
    //     * @return Curso[] Returns an array of Curso objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Curso
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $va lue)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
