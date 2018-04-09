<?php

namespace App\Tests\Controller;

use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class BookingControllerTest
 * @package App\Tests\Controller
 * @group functional
 */
class BookingControllerTest extends WebTestCase
{

    /**
     * @test
     */
    public function it_should_create_booking()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $container->get('doctrine.dbal.default_connection')->query('truncate booking');

        $client->request('POST', '/bookings', [], [], [], json_encode([
            "idUser" => 1,
            "from" => "2018-04-03 18:00",
            "to" => "2018-04-03 19:00",
            "free" => false
        ]));

        $this->assertEquals(
            201,
            $client->getResponse()->getStatusCode(),
            $client->getResponse()->getContent()
        );

        $booking = $container->get('App\Domain\Repository\BookingRepository')->find(
            Uuid::fromString((json_decode($client->getResponse()->getContent(), true)["bookingId"]))
        );

        $this->assertEquals(1, $booking->getIdUser());
        $this->assertEquals("2018-04-03 18:00", $booking->getFrom()->format('Y-m-d H:i'));
        $this->assertEquals("2018-04-03 19:00", $booking->getTo()->format('Y-m-d H:i'));
    }

    /**
     * @test
     */
    public function it_should_fail_when_booking_slots_are_overlapping()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $container->get('doctrine.dbal.default_connection')->query('truncate booking');

        $client->request('POST', '/bookings', [], [], [], json_encode([
            "idUser" => 1,
            "from" => "2018-04-03 18:00",
            "to" => "2018-04-03 20:00",
            "free" => false
        ]));

        $client->request('POST', '/bookings', [], [], [], json_encode([
            "idUser" => 2,
            "from" => "2018-04-03 18:00",
            "to" => "2018-04-03 19:00",
            "free" => false
        ]));

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            'Slot not available',
            json_decode($client->getResponse()->getContent(), true)["message"]
        );

    }

    /**
     * @test
     */
    public function it_should_fail_when_booking_slot_are_shorter_than_1h()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $container->get('doctrine.dbal.default_connection')->query('truncate booking');

        $client->request('POST', '/bookings', [], [], [], json_encode([
            "idUser" => 1,
            "from" => "2018-04-03 19:00",
            "to" => "2018-04-03 19:30",
            "free" => false
        ]));

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            'Slot must be length min 1 hour and max 3 hours',
            json_decode($client->getResponse()->getContent(), true)["message"]
        );

    }

    /**
     * @test
     */
    public function it_should_fail_when_booking_slot_are_longer_than_3h()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $container->get('doctrine.dbal.default_connection')->query('truncate booking');

        $client->request('POST', '/bookings', [], [], [], json_encode([
            "idUser" => 1,
            "from" => "2018-04-03 18:00",
            "to" => "2018-04-03 22:00",
            "free" => false
        ]));

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            'Slot must be length min 1 hour and max 3 hours',
            json_decode($client->getResponse()->getContent(), true)["message"]
        );

    }

    /**
     * @test
     */
    public function it_should_fail_when_booking_slot_time_start_before_9()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $container->get('doctrine.dbal.default_connection')->query('truncate booking');

        $client->request('POST', '/bookings', [], [], [], json_encode([
            "idUser" => 1,
            "from" => "2018-04-03 8:59",
            "to" => "2018-04-03 10:00",
            "free" => false
        ]));

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            'The camp can be booked from 9 to 23',
            json_decode($client->getResponse()->getContent(), true)["message"]
        );

    }

    /**
     * @test
     */
    public function it_should_fail_when_booking_slot_time_end_after_23()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $container->get('doctrine.dbal.default_connection')->query('truncate booking');

        $client->request('POST', '/bookings', [], [], [], json_encode([
            "idUser" => 1,
            "from" => "2018-04-03 22:00",
            "to" => "2018-04-03 23:01",
            "free" => false
        ]));

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertEquals(
            'The camp can be booked from 9 to 23',
            json_decode($client->getResponse()->getContent(), true)["message"]
        );

    }

    /**
     * @test
     */
    public function it_should_be_free_booking_when_booking_is_the_tenth()
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $container->get('doctrine.dbal.default_connection')->query('truncate booking');

        for ($i = 1; $i <= 10; $i++) {
            $client->request('POST', '/bookings', [], [], [], json_encode([
                "idUser" => 1,
                "from" => (new \DateTimeImmutable("2018-04-03 18:00"))->modify("-$i days")->format('Y-m-d H:i'),
                "to" => (new \DateTimeImmutable("2018-04-03 19:00"))->modify("-$i days")->format('Y-m-d H:i'),
                "free" => false
            ]));
        }

        $bookings = $container->get('App\Domain\Repository\BookingRepository')->findAllByUser(1);

        $this->assertTrue($bookings[9]->isFree());
    }
}
