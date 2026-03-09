<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Game\Games;
use App\Game\Statistics;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api.')]
class ShowStatisticController extends AbstractController
{
    public function __construct(
        private readonly Games $games,
        private readonly Statistics $statistics,
    ) {
    }

    #[Route('/games/{gameId}', name: 'show_statistics', methods: ['GET'])]
    #[OA\Get(summary: 'Show the statistics of the specified game')]
    #[IsGranted('show_statistics')]
    public function showStatistics(
        string $gameId,
        #[MapQueryParameter('from_cache')] bool $fromCache = true,
    ): JsonResponse {
        $game = $this->games->find($gameId);

        $statistic = $this->statistics->getStatistic($game, forceCacheRefresh: !$fromCache);

        return $this->json($statistic);
    }
}
