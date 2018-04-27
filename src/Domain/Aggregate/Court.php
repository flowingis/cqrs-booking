<?php

namespace App\Domain\Aggregate;

use App\Domain\Command\CreateBooking;
use App\Domain\Event\BookingCreated;
use App\Domain\Event\PromotionAssigned;
use App\Domain\Exception\SlotLengthInvalid;
use App\Domain\Exception\SlotNotAvailable;
use App\Domain\Exception\SlotTimeInvalid;
use App\Domain\Model\Booking;
use App\Domain\Model\User;
use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Court extends EventSourcedAggregateRoot
{
    const ONE_HOUR_TIMESTAMP = 1 * 60 * 60;
    const THREE_HOURS_TIMESTAMP = 3 * 60 * 60;
    const FIRST_HOUR_BOOKABLE = 9;
    const LAST_HOUR_BOOKABLE = 23;

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
        $this->assertTimeIsValid($command);
        $this->assertSlotIsAvailable($command);

        $this->apply(
            new BookingCreated(
                $command->getCourtId(),
                $command->getUserId(),
                $user->getEmail(),
                $user->getPhone(),
                $command->getFrom(),
                $command->getTo(),
                $command->getBookingUuid()
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

    /**
     * @param int           $userId
     * @param UuidInterface $bookingId
     */
    public function assignPromotion(int $userId, UuidInterface $bookingId)
    {
        $bookingPerUser = 0;
        foreach ($this->bookings as $booking) {
            if ($booking->getIdUser() === $userId) {
                $bookingPerUser++;
            }
        }

        if($bookingPerUser === 10){
            $this->apply(
                new PromotionAssigned($userId, $bookingId)
            );
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
     * @return bool
     */
    private function assertTimeIsValid(CreateBooking $command): bool
    {
        $fromHour = intval($command->getFrom()->format('H'), 10);
        $fromMinute = intval($command->getFrom()->format('i'), 10);
        $toHour = intval($command->getTo()->format('H'), 10);
        $toMinute = intval($command->getTo()->format('i'), 10);

        if (self::isHourValid($fromHour, $fromMinute) and self::isHourValid($toHour, $toMinute)) {
            return true;
        }

        throw new SlotTimeInvalid();
    }

    /**
     * @param int $hour
     * @param int $minute
     * @return bool
     */
    private static function isHourValid(int $hour, int $minute): bool
    {
        if ($hour < self::FIRST_HOUR_BOOKABLE) {
            return false;
        }

        if ($hour > self::LAST_HOUR_BOOKABLE or ($hour == self::LAST_HOUR_BOOKABLE and $minute > 0)) {
            return false;
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
