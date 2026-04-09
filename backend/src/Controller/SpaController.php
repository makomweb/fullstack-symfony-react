<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Serves the React Single Page Application.
 * 
 * Behavior depends on APP_ENV:
 * - Development: Redirects to Vite dev server (http://vite:5173) for HMR
 * - Production: Serves pre-built assets from public/dist/
 */
final class SpaController extends AbstractController
{
    public function __construct(
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
        #[Autowire('%spa.backend_api_url%')]
        private readonly string $backendApiUrl,
        #[Autowire('%spa.otel_collector_address%')]
        private readonly string $otelCollectorAddress,
    ) {
    }

    /**
     * Serve the React SPA application.
     * This route is a catch-all that handles all frontend routes not matched by other controllers.
     * Protected by firewall: only authenticated users (ROLE_USER) can access.
     * Unauthenticated users are redirected to /login by the firewall.
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/spa/{reactRouting}', name: 'spa', requirements: ['reactRouting' => '^(?!api|admin|login|logout|game|_).+'], methods: ['GET'])]
    public function index(string $reactRouting = ''): Response
    {
        // In development, redirect to Vite dev server for HMR
        // if ('dev' === $this->environment) {
        //     return new RedirectResponse("http://localhost:5173/", 307);
        // }

        // In production, serve the pre-built Vite index.html with injected env vars
        return $this->serveBuildOutput();
    }

    /**
     * Serve pre-built React app from public/dist/.
     * Reads the Vite-generated index.html and injects environment variables.
     * Also rewrites asset paths from /assets/ to /dist/assets/ (where they actually are).
     */
    private function serveBuildOutput(): Response
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $viteIndexPath = $projectDir . '/public/dist/index.html';

        if (!file_exists($viteIndexPath)) {
            throw $this->createNotFoundException(
                'React app build not found. Run "npm run build" and copy dist/ to public/dist/'
            );
        }

        $content = file_get_contents($viteIndexPath);

        // Rewrite asset paths: /assets/ → /dist/assets/
        // Vite generates paths like /assets/index-HASH.js but they're actually at /dist/assets/
        $content = str_replace('src="/assets/', 'src="/dist/assets/', $content);
        $content = str_replace('href="/assets/', 'href="/dist/assets/', $content);

        // Inject environment variables as window global variables
        $injectionScript = sprintf(
            '<script>window.BACKEND_API_URL = %s; window.OTEL_COLLECTOR_ADDRESS = %s;</script>',
            json_encode($this->backendApiUrl),
            json_encode($this->otelCollectorAddress)
        );

        // Insert injection script before closing head tag
        $content = str_replace('</head>', $injectionScript . '</head>', $content);

        return new Response($content, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
