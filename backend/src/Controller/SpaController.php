<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Serves the React Single Page Application.
 * Routes all non-API requests to the built React app, which handles its own routing.
 * Protected by firewall: only authenticated users (ROLE_USER) can access.
 */
final class SpaController extends AbstractController
{
    public function __construct(
        private string $backendApiUrl,
        private string $otelCollectorAddress,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/spa/{reactRouting}', name: 'spa', requirements: ['reactRouting' => '^(?!api|admin|login|logout|_)[a-z0-9/\-]*$'], methods: ['GET'], defaults: ['reactRouting' => ''])]
    public function index(string $reactRouting = ''): Response
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        if (!is_string($projectDir)) {
            throw new \RuntimeException('kernel.project_dir parameter must be a string');
        }

        $viteIndexPath = $projectDir.'/public/dist/index.html';

        if (!file_exists($viteIndexPath)) {
            throw $this->createNotFoundException('React app build not found. Run "make frontend-build"');
        }

        $content = file_get_contents($viteIndexPath);
        if (!is_string($content)) {
            throw new \RuntimeException('Failed to read React app build');
        }

        // Inject environment variables into the HTML
        $content = preg_replace(
            '/<script>window\.BACKEND_API_URL\s*=\s*[^;]+;<\/script>/',
            sprintf('<script>window.BACKEND_API_URL = %s;</script>', json_encode($this->backendApiUrl)),
            $content
        );

        assert(is_string($content), new \RuntimeException('Failed to configure "BACKEND_API_URL"!'));

        $content = preg_replace(
            '/<script>window\.OTEL_COLLECTOR_ADDRESS\s*=\s*[^;]+;<\/script>/',
            sprintf('<script>window.OTEL_COLLECTOR_ADDRESS = %s;</script>', json_encode($this->otelCollectorAddress)),
            $content
        );

        assert(is_string($content), new \RuntimeException('Failed to configure "OTEL_COLLECTOR_ADDRESS"!'));

        return new Response($content, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
