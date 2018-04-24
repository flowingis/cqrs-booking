<?php

namespace App\Domain\Repository;


use Broadway\EventHandling\EventBus;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStore;

class CourtAggregateRepository extends EventSourcingRepository
{
    public function __construct(
        EventStore $eventStore,
        EventBus $eventBus
    ) {
        parent::__construct(
            $eventStore,
            $eventBus,
            '\App\Domain\Aggregate\Court',
            new PublicConstructorAggregateFactory());
    }
}
