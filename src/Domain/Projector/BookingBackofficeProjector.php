<?php

namespace App\Domain\Projector;

use App\Domain\Event\BookingCreated;
use App\Domain\ReadModel\BookingBackoffice;
use App\Domain\Repository\Repository;
use Broadway\ReadModel\Projector;

class BookingBackofficeProjector extends Projector
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * BookingBackofficeProjector constructor.
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param BookingCreated $event
     */
    public function applyBookingCreated(BookingCreated $event)
    {
        $this->repository->save(
            new BookingBackoffice(
                $event->getId(),
                $event->getUserId(),
                $event->getEmail(),
                $event->getPhone(),
                $event->getFrom(),
                $event->getTo()
            )
        );
    }
}
