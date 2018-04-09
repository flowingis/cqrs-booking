<?php

namespace App\Domain;


use App\Domain\Command\CreateBooking;
use App\Domain\Model\Booking;
use App\Domain\Repository\BookingRepository;
use App\Domain\Repository\Repository;
use Broadway\CommandHandling\SimpleCommandHandler;

class BookingCommandHandler extends SimpleCommandHandler
{
    /**
     * @var BookingRepository
     */
    private $bookingRepository;

    /**
     * BookingCommandHandler constructor.
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->bookingRepository = $repository;
    }

    /**
     * @param CreateBooking $command
     *
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function handleCreateBooking(CreateBooking $command)
    {
        $booking = Booking::fromCommand($command);

        $booking->assertSlotLengthIsValid();
        $booking->assertTimeIsValid();

        $bookingOfDay = $this->bookingRepository->findBookingByDay($booking->getFrom());
        foreach ($bookingOfDay as &$b) {
            $booking->assertSlotIsAvailable($b);
        }

        return $this->bookingRepository->save($booking);
    }
}
