<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Invariant\Ensure;
use App\Invariant\InvariantException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EnsureTest extends TestCase
{
    #[Test]
    public function assertion_should_fail(): void
    {
        $this->expectException(InvariantException::class);

        Ensure::that(false);
    }

    #[Test]
    public function should_fail_with_custom_message(): void
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('Custom message here!');
        Ensure::that(false, 'Custom message here!');
    }
}
