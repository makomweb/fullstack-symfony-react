<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class AdminLoginRedirectController extends AbstractController
{
    /**
     * Convenience route for admin login.
     * Redirects to the shared login form with admin email prefilled (via query parameter).
     * The admin can optionally prefill the email field by visiting /admin/login?email=admin@example.com
     */
    #[Route('/admin/login', name: 'admin.login')]
    public function redirectToAdminLogin(): RedirectResponse
    {
        return $this->redirectToRoute('app.login', [
            '_username' => 'admin@example.com',
        ], status: RedirectResponse::HTTP_FOUND);
    }
}
