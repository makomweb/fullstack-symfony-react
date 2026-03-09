<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Game\Command\IncrementScoreCommand;
use App\Game\DenormalizerInterface;
use App\Game\Game;
use App\Game\NormalizerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConverterTest extends KernelTestCase
{
    public function setUp(): void
    {
        self::bootKernel(['environment' => 'test']);
    }

    #[Test]
    public function from_object_to_array(): void
    {
        $converter = self::getContainer()->get(NormalizerInterface::class);
        assert($converter instanceof NormalizerInterface);

        $data = $converter->toArray(new Game(new \DateTimeImmutable(), 'Union', 'Hertha'));

        self::assertArrayHasKey('date_time', $data);
        self::assertEquals('Union', $data['home']);
        self::assertEquals('Hertha', $data['guest']);
    }

    #[Test]
    public function from_array_to_object(): void
    {
        $converter = self::getContainer()->get(DenormalizerInterface::class);
        assert($converter instanceof DenormalizerInterface);

        $data = [
            'game_id' => 'ABCDEF',
            'team' => 'home',
            'player_id' => 56,
            'date_time' => '2024-12-30T13:40:05+00:00',
        ];

        $command = $converter->fromArray($data, IncrementScoreCommand::class);
        assert($command instanceof IncrementScoreCommand);

        self::assertEquals('home', $command->team);
        self::assertEquals('ABCDEF', $command->gameId);
        self::assertSame(56, $command->playerId);
    }
}
