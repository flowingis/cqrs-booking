<?php

namespace App\Domain\Aggregate;

use App\Domain\Command\CreateBooking;
use App\Domain\Event\BookingCreated;
use App\Domain\Model\User;
use Broadway\EventSourcing\EventSourcedAggregateRoot;

class Court extends EventSourcedAggregateRoot
{
    public function createBooking(CreateBooking $command, User $user)
    {
        $this->apply(
            new BookingCreated(
                $command->getCourtId(),
                $command->getUserId(),
                $user->getEmail(),
                $user->getPhone(),
                $command->getFrom(),
                $command->getTo()
            )
        );
    }

    /**
     * @return string
     */
    public function getAggregateRootId(): string
    {
        return '';
    }
}
