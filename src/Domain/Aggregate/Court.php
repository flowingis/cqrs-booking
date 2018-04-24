<?php

namespace App\Domain\Aggregate;

use Broadway\EventSourcing\EventSourcedAggregateRoot;

class Court extends EventSourcedAggregateRoot
{

    /**
     * @return string
     */
    public function getAggregateRootId(): string
    {
        // TODO: Implement getAggregateRootId() method.
    }
}
