<?php

namespace App\Repository\Forpas;

use App\Entity\Forpas\Curso;
use App\Entity\Forpas\Edicion;
use DateTime;
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
     *
     * @param int $cursoId El ID del curso para el cual se buscan las ediciones.
     * @return Edicion[] Un array de objetos `Edicion` que cumplen con el criterio de búsqueda.
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
     * Método para encontrar todas las ediciones asociadas a cursos de un año específico.
     *
     * @param int $year El año para el cual se buscan las ediciones (e.g., 2024).
     * @return Edicion[] Un array de objetos `Edicion` que cumplen con el criterio de búsqueda.
     */
    public function findByYear(int $year): array
    {
        $yearCode = substr((string)$year, -2);

        return $this->createQueryBuilder('e')
            ->leftJoin('e.curso', 'c')
            ->andWhere('c.codigo_curso LIKE :yearCode')
            ->andWhere('LENGTH(c.codigo_curso) = 5')
            ->setParameter('yearCode', $yearCode . '%')
            ->getQuery()
            ->getResult();
    }
    /**
     * Método para obtener todas las ediciones que tienen un estado específico.
     *
     * @param int $estado El valor del estado por el cual se filtran las ediciones.
     * @return Edicion[] Un array de objetos `Edicion` que cumplen con el criterio de búsqueda.
     */
    public function findByEstado(int $estado): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.estado = :estado')
            ->setParameter('estado', $estado)
            ->orderBy('e.fecha_inicio', 'ASC')
            ->getQuery()
            ->getResult();
    }
    /**
     * Método para obtener el primer código de edición libre.
     *
     * @param string $codigoCurso Código del curso
     * @return string|null Retorna el primer código de edición libre en el formato "codigoCurso/XX"
     */
    public function findPrimerCodigoEdicionLibre(string $codigoCurso): ?string
    {
        $result = $this->createQueryBuilder('e')
            ->select('e.codigo_edicion')
            ->join('e.curso', 'c')
            ->where('c.codigo_curso = :codigoCurso')
            ->setParameter('codigoCurso', $codigoCurso)
            ->orderBy('e.codigo_edicion', 'ASC')
            ->getQuery()
            ->getResult();

        $edicionesExistentes = array_map(function($item) use ($codigoCurso) {
            return (int) substr($item['codigo_edicion'], strlen($codigoCurso) + 1);
        }, $result);

        $siguienteNumeroLibre = 0; // Comenzamos desde 0
        foreach ($edicionesExistentes as $numero) {
            if ($numero !== $siguienteNumeroLibre) {
                break;
            }
            $siguienteNumeroLibre++;
        }

        return sprintf('%s/%02d', $codigoCurso, $siguienteNumeroLibre);
    }

    /**
     * Método para obtener las ediciones de un curso, excluyendo la edición que termina en "/00".
     *
     * @param Curso $curso La entidad del curso para el que se desean obtener las ediciones.
     * @return Edicion[] Retorna un array de objetos de la entidad Edicion que cumplen con el criterio de búsqueda.
     */
    public function findEdicionesSinCeroCero(Curso $curso): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.curso = :curso')
            ->andWhere('e.codigo_edicion NOT LIKE :codigo')
            ->setParameter('curso', $curso)
            ->setParameter('codigo', '%/00')
            ->getQuery()
            ->getResult();
    }

    /**
     * Método para obtener las ediciones con fecha de inicio mayor que hoy.
     *
     * @return Edicion[] Retorna un array de objetos de la entidad Edicion que cumplen con el criterio de búsqueda.
     */
    public function findProximasEdicionesVisibles(): array
    {
        $hoy = (new DateTime())->setTime(0, 0);
        return $this->createQueryBuilder('e')
            ->innerJoin('e.curso', 'c')
            ->where('e.fecha_inicio >= :hoy')
            ->andWhere('c.visible_web = :visibleWeb')
            ->setParameter('hoy', $hoy)
            ->setParameter('visibleWeb', true)
            ->getQuery()
            ->getResult();
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
