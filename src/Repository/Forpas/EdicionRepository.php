<?php

namespace App\Repository\Forpas;

use App\Entity\Forpas\Edicion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Edicion>
 */
class EdicionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Edicion::class);
    }
    /**
     * Método para obtener todas las ediciones de un curso específico
     * @return Edicion[]
     */
    public function findByCurso(int $cursoId): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.curso', 'c')
            ->addSelect('c')
            ->where('c.id = :cursoId')
            ->setParameter('cursoId', $cursoId)
            ->getQuery()
            ->getResult();
    }
    /**
     * Método para obtener todas las ediciones con sus cursos asociados
     * @return Edicion[]
     */
    public function findAllWithCursos(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.curso', 'c')
            ->addSelect('c')
            ->getQuery()
            ->getResult();
    }
    /**
     * Método para obtener el primer código de edición libre.
     *
     * @param string $codigoCurso Código del curso
     * @return string|null Retorna el primer código de edición libre en el formato "codigoCurso/XX"
     */
    public function findPrimerCodigoEdicionLibre($codigoCurso): ?string
    {
        // Obtenemos todas las ediciones para el curso en orden ascendente
        $result = $this->createQueryBuilder('e')
            ->select('e.codigo_edicion')
            ->join('e.curso', 'c')
            ->where('c.codigo_curso = :codigoCurso')
            ->setParameter('codigoCurso', $codigoCurso)
            ->orderBy('e.codigo_edicion', 'ASC')
            ->getQuery()
            ->getResult();

        // Extraemos los números de edición en forma de enteros
        $edicionesExistentes = array_map(function($item) use ($codigoCurso) {
            return (int) substr($item['codigo_edicion'], strlen($codigoCurso) + 1);
        }, $result);

        // Buscamos el primer número faltante en la secuencia
        $siguienteNumeroLibre = 0; // Comenzamos desde 0
        foreach ($edicionesExistentes as $numero) {
            if ($numero !== $siguienteNumeroLibre) {
                break;
            }
            $siguienteNumeroLibre++;
        }

        // Devolvemos el código libre en el formato "codigoCurso/XX"
        return sprintf('%s/%02d', $codigoCurso, $siguienteNumeroLibre);
    }

//    /**
//     * @return Edicion[] Returns an array of Edicion objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Edicion
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
