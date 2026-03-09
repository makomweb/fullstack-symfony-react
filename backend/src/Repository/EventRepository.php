<?php

namespace App\Repository;

use App\Auth\CurrentUser;
use App\Entity\Event as EventEntity;
use App\Game\Event as ContractEvent;
use App\Game\EventStoreInterface;
use App\Game\QueryOptions as Options;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPMolecules\DDD\Attribute\Repository;

#[Repository]
final readonly class EventRepository implements EventStoreInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CurrentUser $currentUser,
    ) {
    }

    public function persist(ContractEvent $event, bool $dontFlush = false): void
    {
        $entity = new EventEntity(
            $this->currentUser->getUuid(),
            $event->subjectType,
            $event->subjectId,
            $event->eventType,
            $event->payload,
            $event->createdAt
        );

        $this->entityManager->persist($entity);

        if (!$dontFlush) {
            $this->entityManager->flush();
        }
    }

    public function reset(): void
    {
        $repo = $this->entityManager->getRepository(EventEntity::class);

        $entities = $repo->findAll();
        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
    }

    /** @return ContractEvent[] */
    public function getEvents(Options $options = new Options()): array
    {
        $events = $this
            ->createQueryBuilder($options)
            ->getQuery()
            ->getResult();

        assert(is_array($events));

        return array_map(
            static function ($entity) {
                assert($entity instanceof EventEntity);

                return new ContractEvent(
                    $entity->getSubjectType(),
                    $entity->getSubjectId(),
                    $entity->getEventType(),
                    $entity->getPayload(),
                    $entity->getCreatedAt()
                );
            },
            $events
        );
    }

    private function createQueryBuilder(Options $options): QueryBuilder
    {
        $repository = $this->entityManager->getRepository(EventEntity::class);

        $builder = $repository
            ->createQueryBuilder('e')
            ->where('e.createdBy = :uuid')
            ->orderBy('e.createdAt', 'ASC')
            ->setParameter('uuid', $this->currentUser->getUuid());

        if (!empty($options->subjectTypes)) {
            $builder = $builder
                ->andWhere('e.subjectType IN (:subjectTypes)')
                ->setParameter('subjectTypes', $options->subjectTypes);
        }

        if (empty($options->subjectTypes) && !empty($options->subjectIds)) {
            $builder = $builder
                ->andWhere('e.subjectId IN (:subjectIds)')
                ->setParameter('subjectIds', $options->subjectIds);
        } elseif (!empty($options->subjectIds)) {
            $builder = $builder
                // Attention: Use andWhere here!
                ->andWhere('e.subjectId IN (:subjectIds)')
                ->setParameter('subjectIds', $options->subjectIds);
        }

        if (empty($options->subjectTypes) && empty($options->subjectIds) && !empty($options->eventTypes)) {
            $builder = $builder
                ->andWhere('e.subjectId IN (:subjectIds)')
                ->setParameter('subjectIds', $options->subjectIds);
        } elseif (!empty($options->eventTypes)) {
            $builder = $builder
                // Attention: Use andWhere here!
                ->andWhere('e.eventType IN (:eventTypes)')
                ->setParameter('eventTypes', $options->eventTypes);
        }

        return $builder;
    }
}
