<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Note:
     * This method is implemented here to override the basic
     * functionality - it is to fix an error in production builds
     * where the ProjectDir was pointing to the wrong folder.
     *
     * The error was:
     * NOTICE: PHP message: 2026-03-07T09:29:50+00:00
     * [critical] Uncaught Exception:
     * Unable to create the "cache" directory (/var/www/project/src/var/cache/prod).
     *
     * ... it shows that for some unknown reason Symfony tries to create
     * the cache dir in the wrong directory.
     */
    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }
}
