<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Controller\CommandFactory;
use App\Game\Command\AddGameCommand;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CommandFactoryTest extends KernelTestCase
{
    public function setUp(): void
    {
        self::bootKernel(['environment' => 'test']);
    }

    #[Test]
    public function mapping_should_work(): void
    {
        $factory = self::getContainer()->get(CommandFactory::class);
        assert($factory instanceof CommandFactory);

        $command = $factory->create(AddGameCommand::class, [
            'date_time' => '2024-12-22T11:30:03+00:00',
            'home' => 'Union',
            'guest' => 'Hertha',
        ]);

        self::assertInstanceOf(AddGameCommand::class, $command);
        self::assertEquals($command->dateTime, new \DateTimeImmutable('2024-12-22T11:30:03+00:00'));
        self::assertEquals($command->home, 'Union');
        self::assertEquals($command->guest, 'Hertha');
    }
}
