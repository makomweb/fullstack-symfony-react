<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SpaController extends AbstractController
{
    public function __construct(
        private readonly string $backendApiUrl,
        private readonly string $otelCollectorAddress,
    ) {
    }

    /**
     * Serve the React SPA application.
     * This route is a catch-all that handles all frontend routes not matched by other controllers.
     */
    #[Route('/{reactRouting}', name: 'spa', requirements: ['reactRouting' => '^(?!api|admin|login|logout|game|_).+'], methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('spa/index.html.twig', [
            'backend_api_url' => $this->backendApiUrl,
            'otel_collector_address' => $this->otelCollectorAddress,
        ]);
    }
}
