<?php
/**
 * Created by PhpStorm.
 * User: saverio
 * Date: 03/04/18
 * Time: 15.04
 */

namespace App\Tests\Controller;

use App\Domain\Model\Booking;
use PHPUnit\Framework\TestCase;

class BookingTest extends TestCase
{
    /**
     * @test
     */
    public function the_slot_should_be_available()
    {
        $booking1 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 17:00",
            "to" => "2018-04-03 18:00",
            "free" => false
        ]);

        $booking2 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 19:00",
            "to" => "2018-04-03 20:00",
            "free" => false
        ]);

        $booking3 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 18:00",
            "to" => "2018-04-03 19:00",
            "free" => false
        ]);

        $this->assertTrue($booking1->assertSlotIsAvailable($booking3));
        $this->assertTrue($booking2->assertSlotIsAvailable($booking3));
    }

    public function getUnavailableBooking()
    {
        return [
            "booking1" => [[
                "idUser" => 1,
                "from" => "2018-04-03 16:00",
                "to" => "2018-04-03 18:00",
                "free" => false
            ]],
            "booking2" => [[
                "idUser" => 1,
                "from" => "2018-04-03 18:00",
                "to" => "2018-04-03 19:00",
                "free" => false
            ]],
            "booking3" => [[
                "idUser" => 1,
                "from" => "2018-04-03 19:00",
                "to" => "2018-04-03 21:00",
                "free" => false
            ]]];
    }

    /**
     * @test
     * @expectedException \App\Domain\Exception\SlotNotAvailable
     * @dataProvider getUnavailableBooking
     */
    public function the_slot_should_be_unavailable($notAvailableBookingData)
    {
        $booking4 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 17:00",
            "to" => "2018-04-03 20:00",
            "free" => false
        ]);

        $notAvailableBooking = Booking::fromArray($notAvailableBookingData);
        $notAvailableBooking->assertSlotIsAvailable($booking4);
    }

    /**
     * @test
     */
    public function the_slot_length_should_be_valid()
    {
        $booking1 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 16:00",
            "to" => "2018-04-03 17:00",
            "free" => false
        ]);

        $booking2 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 16:00",
            "to" => "2018-04-03 18:00",
            "free" => false
        ]);

        $booking3 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 16:00",
            "to" => "2018-04-03 19:00",
            "free" => false
        ]);

        $this->assertTrue($booking1->assertSlotLengthIsValid());
        $this->assertTrue($booking2->assertSlotLengthIsValid());
        $this->assertTrue($booking3->assertSlotLengthIsValid());
    }

    /**
     * @test
     * @expectedException \App\Domain\Exception\SlotLengthInvalid
     */
    public function the_slot_length_should_be_invalid()
    {
        $booking1 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 16:00",
            "to" => "2018-04-03 16:59",
            "free" => false
        ]);

        $booking2 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 18:00",
            "to" => "2018-04-03 21:01",
            "free" => false
        ]);

        $booking1->assertSlotLengthIsValid();
        $booking2->assertSlotLengthIsValid();
    }

    /**
     * @test
     */
    public function the_slot_time_should_be_valid()
    {
        $booking1 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 9:00",
            "to" => "2018-04-03 10:00",
            "free" => false
        ]);

        $booking2 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 12:00",
            "to" => "2018-04-03 13:00",
            "free" => false
        ]);

        $booking3 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 21:00",
            "to" => "2018-04-03 23:00",
            "free" => false
        ]);

        $this->assertTrue($booking1->assertTimeIsValid());
        $this->assertTrue($booking2->assertTimeIsValid());
        $this->assertTrue($booking3->assertTimeIsValid());
    }

    /**
     * @test
     * @expectedException \App\Domain\Exception\SlotTimeInvalid
     */
    public function the_slot_time_should_be_invalid()
    {
        $booking1 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 8:59",
            "to" => "2018-04-03 10:00",
            "free" => false
        ]);

        $booking2 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 22:00",
            "to" => "2018-04-03 23:01",
            "free" => false
        ]);

        $booking3 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 23:00",
            "to" => "2018-04-04 01:00",
            "free" => false
        ]);

        $booking1->assertTimeIsValid();
        $booking2->assertTimeIsValid();
        $booking3->assertTimeIsValid();
    }

}