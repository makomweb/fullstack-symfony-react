<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * API endpoint to check current user session.
 * Used by frontend's checkRememberMeAsync() during app initialization.
 * Required for session verification when user returns with remember-me cookie.
 */
// TODO I don't think this is useful anymore. Check if we can remove it!
// TODO We need to have an API logout! The modern app is not able to logout at the moment.
#[Route('/api', name: 'api.')]
class MeController extends AbstractController
{
    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'error' => 'Please login first!',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'user' => $user->getUserIdentifier(),
        ]);
    }
}
