<?php

namespace App\Security;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Listener that intercepts logout and redirects to login with preserved referrer
 * as _target_path so users can be redirected back to the same app after re-login.
 */
#[AsEventListener(event: LogoutEvent::class)]
class LogoutEventListener
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function __invoke(LogoutEvent $event): void
    {
        $request = $event->getRequest();

        // Try to get the referrer to determine where user came from
        $referer = $request->headers->get('Referer');
        $targetPath = '/spa'; // Default to modern app

        if ($referer) {
            // Parse the referrer URL to extract just the path
            $refererPath = parse_url($referer, PHP_URL_PATH);
            // Preserve /spa or /game paths as target
            if ($refererPath && (str_starts_with($refererPath, '/spa') || str_starts_with($refererPath, '/game'))) {
                $targetPath = $refererPath;
            }
        }

        // Build login URL with _target_path query parameter
        $loginUrl = $this->urlGenerator->generate('app.login', [
            '_target_path' => $targetPath,
        ]);

        $event->setResponse(new RedirectResponse($loginUrl));
    }
}
