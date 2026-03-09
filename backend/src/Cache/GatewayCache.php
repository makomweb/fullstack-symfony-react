<?php

declare(strict_types=1);

namespace App\Cache;

use App\Game\CacheInterface as AdapterInterface;
use App\Game\CurrentUserInterface;
use PHPMolecules\DDD\Attribute\Service;
use Symfony\Contracts\Cache\CacheInterface as SymfonyCacheInterface;

#[Service]
final readonly class GatewayCache // implements AdapterInterface
{
    public function __construct(
        private SymfonyCacheInterface $sharedCache,
        private CurrentUserInterface $currentUser,
    ) {
    }

    public function getValue(string $key, callable $computeValue, bool $forceCacheRefresh = false): mixed
    {
        return $this->sharedCache->get(
            $this->getUserSpecificKey($key),
            $computeValue,
            // INF = immediate refresh, 1.0 = optimial cache stampede prevention
            $forceCacheRefresh ? INF : 1.0
        );
    }

    public function store(string $key, mixed $value): void
    {
        throw new \Exception('Not implemented!');
    }

    public function remove(string $key): void
    {
        throw new \Exception('Not implemented!');
    }

    private function getUserSpecificKey(string $key): string
    {
        return hash('sha256', $this->currentUser->getUuid().$key);
    }
}
