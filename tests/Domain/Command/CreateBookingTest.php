<?php

namespace App\Tests\Domain\Command;

use App\Domain\BookingCommandHandler;
use App\Domain\Command\CreateBooking;
use App\Domain\Event\BookingCreated;
use App\Domain\Model\User;
use App\Domain\Repository\CourtAggregateRepository;
use App\Domain\Repository\UserRepository;
use Broadway\CommandHandling\CommandHandler;
use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBus;
use Broadway\EventStore\EventStore;
use Ramsey\Uuid\Uuid;

class CreateBookingTest extends CommandHandlerScenarioTestCase
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @test
     */
    public function should_create_valid_booking_if_court_is_available()
    {
        $courtId = Uuid::uuid4();
        $userId = 1;
        $from = new \DateTimeImmutable('2018-03-01 16:00');
        $to = new \DateTimeImmutable('2018-03-01 17:00');
        $email = 'banana@example.com';
        $phone = '3296734555';

        $createBooking = new CreateBooking(
            $courtId,
            $userId,
            $from,
            $to,
            false
        );

        $this->userRepository->find($userId)->willReturn(User::fromArray([
            'id' => $userId,
            'email' => $email,
            'phone' => $phone
        ]));

        $this->scenario
            ->given()
            ->when($createBooking)
            ->then([
                new BookingCreated(
                    $createBooking->getCourtId(),
                    $createBooking->getUserId(),
                    $email,
                    $phone,
                    $createBooking->getFrom(),
                    $createBooking->getTo()
                )
            ]);
    }

    /**
     * @test
     * @expectedException \App\Domain\Exception\SlotNotAvailable
     */
    public function should_not_create_booking_for_not_available_slots()
    {
        $courtId  = Uuid::uuid4();
        $bookingUuid = Uuid::uuid4();
        $userId1 = 1;
        $userId2 = 2;
        $email = 'banana@example.com';
        $phone = '3296734555';

        $bookingCreated1 = new BookingCreated(
            $courtId,
            $userId1,
            $email,
            $phone,
            new \DateTimeImmutable('2018-03-01 16:00'),
            new \DateTimeImmutable('2018-03-01 17:00'),
            Uuid::uuid4()
        );
        $bookingCreated2 = new BookingCreated(
            $courtId,
            $userId1,
            $email,
            $phone,
            new \DateTimeImmutable('2018-03-01 17:00'),
            new \DateTimeImmutable('2018-03-01 18:00'),
            Uuid::uuid4()
        );

        $createBooking = new CreateBooking(
            $courtId,
            $userId2,
            new \DateTimeImmutable('2018-03-01 17:00'),
            new \DateTimeImmutable('2018-03-01 19:00'),
            false,
            $bookingUuid
        );

        $this->userRepository->find($userId2)->willReturn(User::fromArray([
            'id' => $userId2,
            'email' => 'anans@example.com',
            'phone' => '3245678987'
        ]));

        $this->scenario
            ->given([$bookingCreated1, $bookingCreated2])
            ->when($createBooking);
    }

    /**
     * Create a command handler for the given scenario test case.
     *
     * @param EventStore $eventStore
     * @param EventBus   $eventBus
     *
     * @return CommandHandler
     */
    protected function createCommandHandler(EventStore $eventStore, EventBus $eventBus): CommandHandler
    {
        $aggregateRepository = new CourtAggregateRepository($eventStore, $eventBus);
        $this->userRepository = $this->prophesize(UserRepository::class);

        return new BookingCommandHandler($aggregateRepository, $this->userRepository->reveal());
    }
}
