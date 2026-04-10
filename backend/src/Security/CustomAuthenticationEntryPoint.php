<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Custom authentication entry point that preserves the original request URL
 * as _target_path so users are redirected back after successful login.
 */
class CustomAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        // Get the original URL that triggered the authentication requirement
        $targetPath = $request->getPathInfo();
        
        // If we're already at /login, try to extract target from referrer or use /spa as fallback
        if ($targetPath === '/login') {
            $referer = $request->headers->get('Referer');
            if ($referer) {
                // Parse the referrer URL to extract just the path
                $refererPath = parse_url($referer, PHP_URL_PATH);
                // Only use /spa or /game paths, not login or other pages
                if ($refererPath && (str_starts_with($refererPath, '/spa') || str_starts_with($refererPath, '/game'))) {
                    $targetPath = $refererPath;
                }
            }
            // Fallback to /spa
            if ($targetPath === '/login') {
                $targetPath = '/spa';
            }
        }
        
        // Build login URL with _target_path query parameter
        $loginUrl = $this->urlGenerator->generate('app.login', [
            '_target_path' => $targetPath,
        ]);

        // Create a redirect response
        return new Response(
            "<!DOCTYPE html>\n<html>\n<head>\n" .
            "<meta charset=\"UTF-8\" />\n" .
            "<meta http-equiv=\"refresh\" content=\"0;url='$loginUrl'\" />\n" .
            "<title>Redirecting to login</title>\n" .
            "</head>\n<body>\n" .
            "Redirecting to <a href=\"$loginUrl\">login</a>.\n" .
            "</body>\n</html>",
            302,
            ['Location' => $loginUrl]
        );
    }
}
