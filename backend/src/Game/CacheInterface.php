<?php

declare(strict_types=1);

namespace App\Game;

interface CacheInterface
{
    /**
     * Fetches a value from the cache or computes it when it is not present.
     *
     * @param callable():mixed $computeValue
     */
    public function getValue(string $key, callable $computeValue, bool $forceCacheRefresh = false): mixed;

    /** Stores a value into the cache. */
    public function store(string $key, mixed $value): void;

    /** Removes an item from the cache. */
    public function remove(string $key): void;
}
