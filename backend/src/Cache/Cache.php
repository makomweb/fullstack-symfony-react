<?php

declare(strict_types=1);

namespace App\Cache;

use App\Game\CacheInterface as AdapterInterface;
use App\Game\CurrentUserInterface;
use App\Invariant\Ensure;
use PHPMolecules\DDD\Attribute\Service;
use Psr\Cache\CacheItemPoolInterface;

#[Service]
final readonly class Cache implements AdapterInterface
{
    public function __construct(
        private CacheItemPoolInterface $sharedCache,
        private CurrentUserInterface $currentUser,
    ) {
    }

    public function getValue(string $key, callable $computeValue, bool $forceCacheRefresh = false): mixed
    {
        if ($forceCacheRefresh) {
            $this->remove($key);
        }

        $item = $this->sharedCache->getItem($this->getUserSpecificKey($key));
        if ($item->isHit()) {
            return $item->get();
        }

        $value = $computeValue();
        $item->set($value);
        $this->sharedCache->save($item);

        return $value;
    }

    public function store(string $key, mixed $value): void
    {
        $item = $this->sharedCache->getItem($this->getUserSpecificKey($key));
        $item->set($value);
        $this->sharedCache->save($item);
    }

    public function remove(string $key): void
    {
        Ensure::that(
            $this->sharedCache->deleteItem($this->getUserSpecificKey($key)),
            'Unexpected error during cache delete!'
        );
    }

    private function getUserSpecificKey(string $key): string
    {
        return hash('sha256', $this->currentUser->getUuid().$key);
    }
}
