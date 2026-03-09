<?php

declare(strict_types=1);

namespace App\Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tactix\Analyzer\Class\YieldClassNames;

class YieldClassNamesTest extends TestCase
{
    #[Test]
    public function classes_in_core_domain(): void
    {
        $folder = realpath(__DIR__.'/../../src/Game');
        assert(is_string($folder));

        /** @var array<class-string> $classes */
        $classes = iterator_to_array(
            YieldClassNames::for($folder)
        );

        $expected = 38;
        self::assertCount($expected, $classes, sprintf('There should be "%d" classes in "%s"', $expected, $folder));
    }
}
