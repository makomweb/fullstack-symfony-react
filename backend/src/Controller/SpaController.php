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

        // Rewrite asset paths: /assets/ → /dist/assets/
        // Vite generates relative paths /assets/* but they are served from /dist/assets/ by nginx
        $content = str_replace('src="/assets/', 'src="/dist/assets/', $content);
        $content = str_replace('href="/assets/', 'href="/dist/assets/', $content);

        return new Response($content, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
