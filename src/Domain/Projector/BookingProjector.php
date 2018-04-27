<?php

namespace App\Domain\Projector;

use App\Domain\Event\BookingCreated;
use App\Domain\Event\PromotionAssigned;
use App\Domain\ReadModel\Booking;
use App\Domain\Repository\Repository;
use Broadway\ReadModel\Projector;

class BookingProjector extends Projector
{
    /**
     * @var (Repository)
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
            new Booking(
                $event->getCourtId(),
                $event->getUserId(),
                $event->getFrom(),
                $event->getTo(),
                $event->getBookingUuid()
            )
        );
    }

    /**
     * @param PromotionAssigned $event
     */
    public function applyPromotionAssigned(PromotionAssigned $event)
    {
        /** @var Booking $booking */
        $booking = $this->repository->findByBookingId($event->getBookingUuid());

        $booking->free();

        $this->repository->update($booking);
    }
}
