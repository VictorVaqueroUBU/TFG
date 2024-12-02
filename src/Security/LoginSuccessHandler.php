<?php
namespace App\Security;

use App\Entity\Sistema\Usuario;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        /** @var Usuario|null $user */
        $user = $token->getUser();

        if (!$user->isVerified()) {
            return new RedirectResponse($this->urlGenerator->generate('intranet_change_password'));
        }

        return new RedirectResponse($this->urlGenerator->generate('intranet_forpas'));
    }
}
