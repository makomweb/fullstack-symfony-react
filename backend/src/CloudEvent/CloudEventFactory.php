<?php

declare(strict_types=1);

namespace App\CloudEvent;

use App\Entity\Event;
use PHPMolecules\DDD\Attribute\Factory;
use Symfony\Component\Serializer\SerializerInterface;

#[Factory]
final readonly class CloudEventFactory
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function fromCloudEvent(CloudEvent $event): Event
    {
        return $this->serializer->deserialize(
            $event->data, Event::class, $event->datacontenttype
        );
    }

    public function asCloudEvent(Event $event): CloudEvent
    {
        return new CloudEvent(
            specversion: '1.0.2',
            type: 'com.demo',
            source: strtolower(str_replace('\\', '.', get_class($event))),
            subject: $event->getSubjectId(),
            id: $event->getId()->toString(),
            time: $event->getCreatedAt()->format('Y-m-d\TH:i:s\Z'),
            datacontenttype: 'json',
            data: $this->serializer->serialize($event, 'json'),
        );
    }
}
