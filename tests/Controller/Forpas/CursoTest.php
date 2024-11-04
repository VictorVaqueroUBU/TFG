<?php

namespace App\Tests\Controller\Forpas;

use App\Entity\Forpas\Curso;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CursoTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testHorasMenorQueHorasVirtuales(): void
    {
        // Crear una instancia de Curso con valores inválidos para validar
        $curso = new Curso();
        $curso->setHoras(10);
        $curso->setHorasVirtuales(15); // Valor mayor que horas para provocar la violación
        $curso->setNombreCurso('Nombre del curso');
        $curso->setCodigoCurso('24001');

        // Validar la entidad
        $violations = $this->validator->validate($curso);

        // Comprobar que hay al menos una violación
        $this->assertGreaterThan(0, count($violations), 'Se esperaba al menos una violación de validación.');

        // Verificar que la violación es la esperada
        $violation = $violations[0];
        $this->assertSame('El valor de horas no puede ser menor que horas virtuales.', $violation->getMessage());
        $this->assertSame('horas', $violation->getPropertyPath());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->validator);
    }
}
