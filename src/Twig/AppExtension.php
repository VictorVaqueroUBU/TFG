<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use DateTimeInterface;

/**
 * Servicio para añadir filtros a las plantillas Twig.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }
    /** Añadimos variables globales. */
    public function getGlobals(): array
    {
        $titulo = null;
        $request = $this->requestStack->getCurrentRequest();
        if ($request instanceof Request) {
            // Obtenemos el título de la página definido en el controlador
            $titulo = (string) $request->attributes->get('titulo');
        }

        return [
            'titulo' => $titulo,
        ];
    }

    /** Añadimos funciones. */
    public function getFunctions(): array
    {
        return [
            // Devuelve los días de diferencia entre 2 fechas
            new TwigFunction(
                'diff_days',
                static fn (DateTimeInterface $inicio, DateTimeInterface $fin): int => $inicio->diff($fin)->days + 1
            ),
        ];
    }

    /** Añadimos filtros específicos. */
    public function getFilters(): array
    {
        return [
            new TwigFilter('edicionEstadoTexto', [$this, 'edicionEstadoTexto']),
            new TwigFilter('formadorRJTexto', [$this, 'formadorRJTexto']),
        ];
    }
    public function edicionEstadoTexto(int $estado): string
    {
        return match ($estado) {
            0 => 'Abierta',
            1 => 'Evaluada',
            2 => 'Certificada',
            default => 'Estado desconocido',
        };
    }
    public function formadorRJTexto(?int $formadorRJ): string
    {
        if ($formadorRJ === null) {
            return 'Pendiente de información';
        }

        return match ($formadorRJ) {
            1 => 'Empleado Público',
            2 => 'Autónomo en activo',
            3 => 'Empresa/Sociedad',
            4 => 'Persona física',
            default => 'Tipo desconocido',
        };
    }
}
