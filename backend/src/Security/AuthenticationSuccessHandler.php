<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

readonly class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

    public function __construct(private RouterInterface $router)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), 'main');

        // If a target path was stored in session (via _target_path parameter), use it
        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        // Otherwise use role-based defaults
        $user = $token->getUser();
        if ($user && in_array('ROLE_ADMIN', $user->getRoles(), strict: true)) {
            return new RedirectResponse($this->router->generate('admin'));
        }

        return new RedirectResponse($this->router->generate('app.index_games'));
    }
}
