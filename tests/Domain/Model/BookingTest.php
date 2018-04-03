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

}