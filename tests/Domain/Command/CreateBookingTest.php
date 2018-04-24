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
