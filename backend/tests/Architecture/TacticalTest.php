<?php

declare(strict_types=1);

namespace App\Tests\Architecture;

use App\Game\Event as GameEvent;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tactix\Blacklist;
use Tactix\Violation;
use Tactix\ViolationException;
use Tactix\YieldViolations;

class TacticalTest extends KernelTestCase
{
    // #[Test] // Reason: core domain classes do not carry DDD tags yet!
    public function check_core_domain(): void
    {
        $folder = realpath(__DIR__.'/../../src/Game');
        assert(is_string($folder) && is_dir($folder));

        $this->expectNotToPerformAssertions();

        $violations = iterator_to_array(self::createYielder()->fromFolder($folder));

        try {
            self::assertNoViolations($violations, "Folder $folder has tactical DDD violations!");
        } catch (ViolationException $ex) {
            var_dump($ex->violations);
            self::fail(
                "There should be no violations in {$folder}!".PHP_EOL.
                '⛔️ Some of the core domain classes depend on'.PHP_EOL.
                'a support or generic domain class - which is prohibited.'.PHP_EOL.
                'Might also be that a core domain class is not tagged with a DDD tactical tag.'
            );
        }
    }

    #[Test]
    public function event_should_not_have_ddd_violations(): void
    {
        $violations = iterator_to_array(self::createYielder()->fromClassName(GameEvent::class));

        self::assertEmpty($violations);
    }

    /** @param Violation[] $violations */
    private static function assertNoViolations(array $violations, string $message): void
    {
        if (!empty($violations)) {
            throw new ViolationException($message, $violations);
        }
    }

    private static function createYielder(): YieldViolations
    {
        $blacklist = self::getContainer()->get(Blacklist::class);
        assert($blacklist instanceof Blacklist);

        return new YieldViolations($blacklist);
    }
}
