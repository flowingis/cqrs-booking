<?php

namespace App\Tests\Domain;

use App\Domain\BookingCommandHandler;
use App\Domain\Command\AssignPromotion;
use App\Domain\Command\CreateBooking;
use App\Domain\Model\Booking;
use App\Domain\Model\User;
use App\Domain\Repository\BookingRepository;
use App\Domain\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class BookingCommandHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function should_create_booking()
    {
        $bookingRepository = $this->prophesize(BookingRepository::class);
        $userRepository = $this->prophesize(UserRepository::class);
        $eventBus = $this->prophesize(\Broadway\EventHandling\EventBus::class);
        $from = new \DateTimeImmutable('2018-03-01 15:00');
        $uuid = Uuid::uuid4();
        $userId = 1;
        $expectedBooking = Booking::fromArray([
            'uuid' => $uuid,
            'idUser' => $userId,
            'from' => '2018-03-01 15:00',
            'to' => '2018-03-01 16:00',
            'free' => false
        ]);
        $user = User::fromArray([
            'id' => $userId,
            'email' => 'banana@example.it',
            'phone' => '329430490'
        ]);

        $commandHandler = new BookingCommandHandler(
            $bookingRepository->reveal(),
            $userRepository->reveal(),
            $eventBus->reveal()
        );

        $userRepository->find($userId)->willReturn($user);
        $bookingRepository->findBookingByDay($from)->willReturn([]);
        $bookingRepository->save($expectedBooking)->shouldBeCalled();

        $commandHandler->handleCreateBooking(
            new CreateBooking(
                $uuid,
                1,
                $from,
                new \DateTimeImmutable('2018-03-01 16:00'),
                false
            )
        );
    }

    /**
     * @test
     * @expectedException \App\Domain\Exception\SlotLengthInvalid
     */
    public function should_not_create_booking_for_invalid_slot_length()
    {
        $bookingRepository = $this->prophesize(BookingRepository::class);
        $eventBus = $this->prophesize(\Broadway\EventHandling\EventBus::class);

        $commandHandler = new BookingCommandHandler(
            $bookingRepository->reveal(),
            $this->prophesize(UserRepository::class)->reveal(),
            $eventBus->reveal()
        );

        $commandHandler->handleCreateBooking(
            new CreateBooking(
                Uuid::uuid4(),
                1,
                new \DateTimeImmutable('2018-03-01 15:00'),
                new \DateTimeImmutable('2018-03-01 19:00'),
                false
            )
        );
    }

    /**
     * @test
     * @expectedException \App\Domain\Exception\SlotTimeInvalid
     */
    public function should_not_create_booking_when_court_are_closed()
    {
        $bookingRepository = $this->prophesize(BookingRepository::class);
        $eventBus = $this->prophesize(\Broadway\EventHandling\EventBus::class);

        $commandHandler = new BookingCommandHandler(
            $bookingRepository->reveal(),
            $this->prophesize(UserRepository::class)->reveal(),
            $eventBus->reveal()
        );

        $commandHandler->handleCreateBooking(
            new CreateBooking(
                Uuid::uuid4(),
                1,
                new \DateTimeImmutable('2018-03-01 08:00'),
                new \DateTimeImmutable('2018-03-01 09:00'),
                false
            )
        );
    }

    /**
     * @test
     * @expectedException \App\Domain\Exception\SlotNotAvailable
     */
    public function should_not_create_booking_for_not_available_slots()
    {
        $bookingRepository = $this->prophesize(BookingRepository::class);
        $eventBus = $this->prophesize(\Broadway\EventHandling\EventBus::class);
        $from = new \DateTimeImmutable('2018-03-01 15:00');
        $uuid = Uuid::uuid4();
        $alreadyExistentBooking = Booking::fromArray([
            'uuid' => $uuid,
            'idUser' => 1,
            'from' => '2018-03-01 15:00',
            'to' => '2018-03-01 16:00',
            'free' => false
        ]);

        $commandHandler = new BookingCommandHandler(
            $bookingRepository->reveal(),
            $this->prophesize(UserRepository::class)->reveal(),
            $eventBus->reveal()
        );

        $bookingRepository->findBookingByDay($from)->willReturn([$alreadyExistentBooking]);

        $commandHandler->handleCreateBooking(
            new CreateBooking(
                Uuid::uuid4(),
                1,
                $from,
                new \DateTimeImmutable('2018-03-01 16:00'),
                false
            )
        );
    }

    /**
     * @test
     */
    public function should_assign_promotion()
    {
        $bookingRepository = $this->prophesize(BookingRepository::class);
        $eventBus = $this->prophesize(\Broadway\EventHandling\EventBus::class);
        $uuid = Uuid::uuid4();
        $userId = 1;
        $createBooking = new CreateBooking(
            $uuid,
            $userId,
            new \DateTimeImmutable('2018-03-01 08:00'),
            new \DateTimeImmutable('2018-03-01 09:00'),
            false
        );
        $expectedBooking = Booking::fromArray([
            'uuid' => $uuid,
            'idUser' => $userId,
            'from' => '2018-03-01 15:00',
            'to' => '2018-03-01 16:00',
            'free' => false
        ]);

        $commandHandler = new BookingCommandHandler(
            $bookingRepository->reveal(),
            $this->prophesize(UserRepository::class)->reveal(),
            $eventBus->reveal()
        );

        $bookingRepository->find($createBooking->getId())->willReturn($expectedBooking);
        $bookingRepository->findAllByUser($userId)->willReturn([1,2,3,4,5,6,7,8,9,10]);
        $expectedBooking->free();
        $bookingRepository->update($expectedBooking)->shouldBeCalled();

        $commandHandler->handleAssignPromotion(new AssignPromotion($uuid, 1));
    }
}
