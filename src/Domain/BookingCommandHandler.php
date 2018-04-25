<?php

namespace App\Domain;


use App\Domain\Aggregate\Court;
use App\Domain\Command\AssignPromotion;
use App\Domain\Command\CreateBooking;
use App\Domain\Repository\UserRepository;
use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\Repository\Repository;

class BookingCommandHandler extends SimpleCommandHandler
{
    /**
     * @var Repository
     */
    private $courtAggregateRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * BookingCommandHandler constructor.
     *
     * @param Repository     $courtAggregateRepository
     * @param UserRepository $userRepository
     */
    public function __construct(Repository $courtAggregateRepository, UserRepository $userRepository)
    {
        $this->courtAggregateRepository = $courtAggregateRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param CreateBooking $command
     *
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function handleCreateBooking(CreateBooking $command)
    {
        $user = $this->userRepository->find($command->getUserId());

        $courtAggregate = new Court();
        $courtAggregate->createBooking($command, $user);

        $this->courtAggregateRepository->save($courtAggregate);
//        $booking = Booking::fromCommand($command);
//
//        $booking->assertSlotLengthIsValid();
//        $booking->assertTimeIsValid();
//
//        $bookingOfDay = $this->courtAvailabilityAggregateRepository->findBookingByDay($booking->getFrom());
//        foreach ($bookingOfDay as &$b) {
//            $booking->assertSlotIsAvailable($b);
//        }
//
//        $this->courtAvailabilityAggregateRepository->save($booking);
//
//        $user = $this->userRepository->find($command->getUserId());
//
//        $this->eventBus->publish(
//            new DomainEventStream(
//                [
//                    DomainMessage::recordNow(
//                        $command->getId(),
//                        0,
//                        new Metadata([]),
//                        new BookingCreated(
//                            $command->getId(),
//                            $command->getUserId(),
//                            $user->getEmail(),
//                            $user->getPhone(),
//                            $command->getFrom(),
//                            $command->getTo()
//                        )
//                    )
//                ]
//            )
//        );
    }

    public function handleAssignPromotion(AssignPromotion $command)
    {
//        $booking = $this->courtAvailabilityAggregateRepository->find($command->getId());
//
//        if (count($this->courtAvailabilityAggregateRepository->findAllByUser($command->getUserId())) === 10) {
//            $booking->free();
//            $this->courtAvailabilityAggregateRepository->update($booking);
//        }
    }
}
