<?php

namespace App\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

readonly class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private LoggerInterface $logger,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        // Check for _target_path in the request (POST parameter from form)
        $targetPath = $this->getTargetPathFromRequest($request);

        $this->logger->info('AuthenticationSuccessHandler called', [
            'targetPath' => $targetPath,
            'requestData' => [
                'post' => $request->request->all(),
                'query' => $request->query->all(),
            ],
        ]);

        // If a target path was provided in the request, use it
        if ($targetPath && ($this->isValidPath($targetPath) || $this->isValidUrl($targetPath))) {
            $this->logger->info('Redirecting to target path', ['target' => $targetPath]);
            return new RedirectResponse($targetPath);
        }

        // Otherwise use role-based defaults
        $user = $token->getUser();
        if ($user && in_array('ROLE_ADMIN', $user->getRoles(), strict: true)) {
            $adminPath = $this->router->generate('admin');
            $this->logger->info('Redirecting admin to admin panel', ['path' => $adminPath]);
            return new RedirectResponse($adminPath);
        }

        $gamePath = $this->router->generate('app.index_games');
        $this->logger->info('Redirecting user to games', ['path' => $gamePath]);
        return new RedirectResponse($gamePath);
    }

    private function getTargetPathFromRequest(Request $request): ?string
    {
        // Check POST first (from form submission)
        $targetPath = $request->request->get('_target_path');
        if ($targetPath) {
            return $targetPath;
        }

        // Check query string as fallback
        return $request->query->get('_target_path');
    }

    private function isValidPath(string $path): bool
    {
        // Ensure it starts with / (relative path)
        return str_starts_with($path, '/');
    }

    private function isValidUrl(string $url): bool
    {
        // Accept absolute URLs that start with http
        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
    }
}
