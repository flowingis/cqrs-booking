<?php

namespace App\Tests\Projector;

use App\Domain\Event\BookingCreated;
use App\Domain\Projector\BookingBackofficeProjector;
use App\Domain\ReadModel\BookingBackoffice;
use App\Domain\Repository\BookingBackofficeRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class BookingBackofficeProjectorTest extends TestCase
{
    /**
     * @test
     */
    public function should_create_booking_read_model()
    {
        $userId = 1;
        $repository = $this->prophesize(BookingBackofficeRepository::class);
        $projector = new BookingBackofficeProjector($repository->reveal());
        $uuid = Uuid::uuid4();
        $event = new BookingCreated(
            $uuid,
            $userId,
            'user@email.it',
            '0349043904',
            new \DateTimeImmutable('2018-03-01 10:00'),
            new \DateTimeImmutable('2018-03-01 11:00')
        );
        $bookingBackofficeReadModel = new BookingBackoffice(
            $uuid,
            $userId,
            'user@email.it',
            '0349043904',
            new \DateTimeImmutable('2018-03-01 10:00'),
            new \DateTimeImmutable('2018-03-01 11:00')
        );

        $repository->save($bookingBackofficeReadModel)->shouldBeCalled();

        $projector->applyBookingCreated($event);
    }
}
