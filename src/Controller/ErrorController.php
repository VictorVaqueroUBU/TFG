<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use function Symfony\Component\String\u;

/**
 * Clase para mostrar mensajes de error y redirigir a la página adecuada.
 * @author Ramón M. Gómez <ramongomez@us.es>
 */
class ErrorController extends AbstractController
{
    /** @const array<int, string> */
    private const ERRORES = [
        0 => 'Se ha producido un error',
        Response::HTTP_NOT_FOUND => 'Ruta no encontrada',
    ];

    /**
     * @param Throwable $exception Excepción lanzada
     * @param Request $request Petición web
     * @return Response Respuesta web
     */
    public function show(Throwable $exception, Request $request): Response
    {
        $mensaje = $exception instanceof HttpException ? self::ERRORES[$exception->getStatusCode()] ?? $exception->getMessage() : self::ERRORES[0];
        $this->addFlash('warning', $mensaje);

        return $this->redirectToRoute(u($request->getPathInfo())->startsWith('/intranet/') ? 'intranet' : 'inicio');
    }
}
