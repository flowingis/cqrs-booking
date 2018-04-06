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
            "to" => "2018-04-03 18:00"
        ]);

        $booking2 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 19:00",
            "to" => "2018-04-03 20:00"
        ]);

        $booking3 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 18:00",
            "to" => "2018-04-03 19:00"
        ]);

        $this->assertTrue($booking1->isSlotAvailable($booking3));
        $this->assertTrue($booking2->isSlotAvailable($booking3));
    }

    /**
     * @test
     */
    public function the_slot_should_be_unavailable()
    {
        $booking1 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 16:00",
            "to" => "2018-04-03 18:00"
        ]);

        $booking2 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 18:00",
            "to" => "2018-04-03 19:00"
        ]);

        $booking3 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 19:00",
            "to" => "2018-04-03 21:00"
        ]);

        $booking4 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 17:00",
            "to" => "2018-04-03 20:00"
        ]);

        $this->assertFalse($booking1->isSlotAvailable($booking4));
        $this->assertFalse($booking2->isSlotAvailable($booking4));
        $this->assertFalse($booking3->isSlotAvailable($booking4));
    }

    /**
     * @test
     */
    public function the_slot_length_should_be_valid()
    {
        $booking1 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 16:00",
            "to" => "2018-04-03 17:00"
        ]);

        $booking2 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 16:00",
            "to" => "2018-04-03 18:00"
        ]);

        $booking3 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 16:00",
            "to" => "2018-04-03 19:00"
        ]);

        $this->assertTrue(Booking::isSlotLengthValid($booking1));
        $this->assertTrue(Booking::isSlotLengthValid($booking2));
        $this->assertTrue(Booking::isSlotLengthValid($booking3));
    }

    /**
     * @test
     */
    public function the_slot_length_should_be_invalid()
    {
        $booking1 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 16:00",
            "to" => "2018-04-03 16:59"
        ]);

        $booking2 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 18:00",
            "to" => "2018-04-03 21:01"
        ]);

        $this->assertFalse(Booking::isSlotLengthValid($booking1));
        $this->assertFalse(Booking::isSlotLengthValid($booking2));
    }

    /**
     * @test
     */
    public function the_slot_time_should_be_valid()
    {
        $booking1 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 9:00",
            "to" => "2018-04-03 10:00"
        ]);

        $booking2 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 12:00",
            "to" => "2018-04-03 13:00"
        ]);

        $booking3 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 21:00",
            "to" => "2018-04-03 23:00"
        ]);

        $this->assertTrue(Booking::isTimeValid($booking1));
        $this->assertTrue(Booking::isTimeValid($booking2));
        $this->assertTrue(Booking::isTimeValid($booking3));
    }

    /**
     * @test
     */
    public function the_slot_time_should_be_invalid()
    {
        $booking1 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 8:59",
            "to" => "2018-04-03 10:00"
        ]);

        $booking2 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 22:00",
            "to" => "2018-04-03 23:01"
        ]);

        $booking3 = Booking::fromArray([
            "idUser" => 1,
            "from" => "2018-04-03 23:00",
            "to" => "2018-04-04 01:00"
        ]);

        $this->assertFalse(Booking::isTimeValid($booking1));
        $this->assertFalse(Booking::isTimeValid($booking2));
        $this->assertFalse(Booking::isTimeValid($booking3));
    }

}