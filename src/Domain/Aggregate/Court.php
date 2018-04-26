<?php

namespace App\Domain\Aggregate;

use App\Domain\Command\CreateBooking;
use App\Domain\Event\BookingCreated;
use App\Domain\Exception\SlotNotAvailable;
use App\Domain\Model\Booking;
use App\Domain\Model\User;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Ramsey\Uuid\UuidInterface;

class Court extends EventSourcedAggregateRoot
{
    /**
     * @var Booking[]
     */
    private $bookings = [];
    /**
     * @var UuidInterface
     */
    private $id;

    public function createBooking(CreateBooking $command, User $user)
    {
        $this->assertSlotIsAvailable($command);

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
     * @param CreateBooking $createBooking
     */
    private function assertSlotIsAvailable(CreateBooking $createBooking)
    {
        /** @var Booking $booking */
        foreach ($this->bookings as $booking) {
            if ($booking->getFrom()->getTimestamp() >= $createBooking->getTo()->getTimestamp())
            {
                continue;
            }

            if ($booking->getTo()->getTimestamp() <= $createBooking->getFrom()->getTimestamp())
            {
                continue;
            }

            throw new SlotNotAvailable();
        }
    }

    protected function applyBookingCreated(BookingCreated $event)
    {
        $this->id = $event->getCourtId();

        $this->bookings[] = Booking::fromArray(
            [
                'uuid' => $event->getCourtId(),
                'idUser' => $event->getUserId(),
                'from' => $event->getFrom()->format('Y-m-d H:i'),
                'to' => $event->getTo()->format('Y-m-d H:i'),
                'free' => false
            ]
        );
    }

    /**
     * @return string
     */
    public function getAggregateRootId(): string
    {
        return (string)$this->id;
    }
}
