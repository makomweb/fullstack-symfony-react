<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\CloudEvent\CloudEventFactory;
use App\Entity\Event;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

class EventPersistedTest extends KernelTestCase
{
    public function setUp(): void
    {
        self::bootKernel(['environment' => 'test']);
    }

    #[Test]
    public function cloud_event(): void
    {
        $event = new Event(
            createdBy: 'foo@bar.com',
            subjectType: 'my.test.event',
            subjectId: Uuid::v4()->toString(),
            eventType: 'my.event.type',
            payload: ['foo' => 'bar'],
            createdAt: new \DateTimeImmutable()
        );

        $serializer = self::getContainer()->get(SerializerInterface::class);
        assert($serializer instanceof SerializerInterface);

        $factory = new CloudEventFactory($serializer);

        $cloudEvent = $factory->asCloudEvent($event);

        $deserialized = $factory->fromCloudEvent($cloudEvent);

        self::assertInstanceOf(Event::class, $deserialized);
    }
}
