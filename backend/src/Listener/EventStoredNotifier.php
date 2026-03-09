<?php

declare(strict_types=1);

namespace App\Listener;

use App\Entity\Event as EventEntity;
use App\Message\EventPersisted;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Listen to entity persist events and notify the message bus
 * to let the worker refresh the read model.
 *
 * @see https://symfony.com/doc/current/doctrine/events.html#doctrine-entity-listeners
 */
#[AsEntityListener(event: Events::postPersist, method: 'onPostPersist', entity: EventEntity::class)]
final readonly class EventStoredNotifier
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function onPostPersist(EventEntity $entity, PostPersistEventArgs $args): void
    {
        $message = EventPersisted::fromEvent($entity);

        self::addContext($message);
        $this->bus->dispatch($message);
    }

    private static function addContext(EventPersisted $message): void
    {
        $pairs = [];

        TraceContextPropagator::getInstance()->inject($pairs);
        assert(is_array($pairs));

        foreach ($pairs as $key => $value) {
            assert(is_string($value));
            $message->addTraceContext($key, $value);
        }
    }
}
