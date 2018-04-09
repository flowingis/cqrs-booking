<?php

namespace App\Domain;


use App\Domain\Command\AssignPromotion;
use App\Domain\Command\CreateBooking;
use App\Domain\Event\BookingCreated;
use App\Domain\Model\Booking;
use App\Domain\Repository\BookingRepository;
use App\Domain\Repository\Repository;
use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventBus;

class BookingCommandHandler extends SimpleCommandHandler
{
    /**
     * @var BookingRepository
     */
    private $bookingRepository;
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * BookingCommandHandler constructor.
     *
     * @param Repository $repository
     * @param EventBus   $eventBus
     */
    public function __construct(Repository $repository, EventBus $eventBus)
    {
        $this->bookingRepository = $repository;
        $this->eventBus = $eventBus;
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

        $this->bookingRepository->save($booking);

        $this->eventBus->publish(
            new DomainEventStream(
                [
                    DomainMessage::recordNow(
                        $command->getId(),
                        0,
                        new Metadata([]),
                        new BookingCreated($command->getId(), $command->getUserId())
                    )
                ]
            )
        );
    }

    public function handleAssignPromotion(AssignPromotion $command)
    {
        $booking = $this->bookingRepository->find($command->getId());

        if (count($this->bookingRepository->findAllByUser($command->getUserId())) === 10) {
            $booking->free();
            $this->bookingRepository->update($booking);
        }
    }
}
