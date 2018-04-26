<?php

namespace App\Domain\Aggregate;

use App\Domain\Command\CreateBooking;
use App\Domain\Event\BookingCreated;
use App\Domain\Exception\SlotLengthInvalid;
use App\Domain\Exception\SlotNotAvailable;
use App\Domain\Model\Booking;
use App\Domain\Model\User;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Ramsey\Uuid\UuidInterface;

class Court extends EventSourcedAggregateRoot
{
    const ONE_HOUR_TIMESTAMP = 1 * 60 * 60;
    const THREE_HOURS_TIMESTAMP = 3 * 60 * 60;

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
        $this->assertSlotLengthIsValid($command);
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
     * @param CreateBooking $command
     *
     * @return bool
     */
    private function assertSlotLengthIsValid(CreateBooking $command): bool
    {
        $diff = $command->getTo()->getTimestamp() - ($command->getFrom()->getTimestamp());

        if ($diff < self::ONE_HOUR_TIMESTAMP) {
            throw new SlotLengthInvalid();
        }

        if ($diff > self::THREE_HOURS_TIMESTAMP) {
            throw new SlotLengthInvalid();
        }

        return true;
    }

    /**
     * @return string
     */
    public function getAggregateRootId(): string
    {
        return (string)$this->id;
    }
}
