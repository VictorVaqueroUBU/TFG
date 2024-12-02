<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Controlador para redirigir los códigos 403.
 * @author Víctor M. Vaquero <vvm1002@alu.ubu.es>
 */
class AccessDeniedSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;
    private RequestStack $requestStack;

    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AccessDeniedHttpException) {
            $request = $this->requestStack->getCurrentRequest();
            $session = $request->getSession();

            if ($session instanceof Session) {
                $session->getFlashBag()->add('warning', 'No tienes permiso para acceder a esta página.');
            }

            $response = new RedirectResponse($this->router->generate('intranet_forpas'));
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
