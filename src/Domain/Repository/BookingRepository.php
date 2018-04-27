<?php

namespace App\Domain\Repository;


use App\Domain\Exception\ModelNotFound;
use App\Domain\Model\Model;
use App\Domain\ReadModel\Booking;
use Broadway\ReadModel\Identifiable;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SebastianBergmann\Comparator\Book;

/**
 * Class BookingRepository
 * @package App\Domain\Repository
 */
class BookingRepository implements Repository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * BookingRepository constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Identifiable $booking
     */
    public function save(Identifiable $booking): void
    {
        $this->connection->insert('booking', [
            "uuid" => (string)$booking->getId(),
            "id_user" => $booking->getIdUser(),
            "date_from" => $booking->getFrom()->format('Y-m-d H:i'),
            "date_to" => $booking->getTo()->format('Y-m-d H:i'),
            "booking_uuid" => (string)$booking->getBookingUuid()
        ]);
    }

    /**
     * @param Booking $booking
     */
    public function update(Identifiable $booking): void
    {
        $this->connection->update(
            'booking',
            ["free" => $booking->isFree()],
            [
                "uuid" => (string)$booking->getId(),
                "booking_uuid" => (string)$booking->getBookingUuid()
            ]
        );
    }

    /**
     * @param UuidInterface $id
     *
     * @return Identifiable|null
     * @throws \Assert\AssertionFailedException
     */
    public function find(UuidInterface $id) : ?Identifiable
    {
        $bookingData = $this->connection->fetchAssoc(
            'select id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free, booking_uuid from booking where uuid = :id',
            ["id" => $id]
        );

        if ($bookingData) {
            return new Booking(
                Uuid::fromString($bookingData['uuid']),
                $bookingData['idUser'],
                new \DateTimeImmutable($bookingData['from']),
                new \DateTimeImmutable($bookingData['to']),
                Uuid::fromString($bookingData['booking_uuid']),
                $bookingData['free']
            );
        }

        throw new ModelNotFound();
    }

    /**
     * @param \DateTimeImmutable $day
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findBookingByDay(\DateTimeImmutable $day) : array
    {
        $bookingsData = $this->connection->executeQuery(
            'SELECT id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free FROM booking WHERE DATE(date_from)=:date',
            ["date" => $day->format('Y-m-d')]);

        $result = array();

        foreach ($bookingsData->fetchAll() as &$bookingData) {
            $result[] = Booking::fromArray($bookingData);
        }

        return $result;
    }

    /**
     * @param int $userId
     * @return Booking[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findAllByUser(int $userId) : array
    {
        $bookingsData = $this->connection->executeQuery(
            'SELECT id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free, booking_uuid FROM booking WHERE id_user=:id ORDER BY id ASC',
            ["id" => $userId]);

        $result = array();

        foreach ($bookingsData->fetchAll() as &$bookingData) {
            $result[] = new Booking(
                Uuid::fromString($bookingData['uuid']),
                $bookingData['idUser'],
                new \DateTimeImmutable($bookingData['from']),
                new \DateTimeImmutable($bookingData['to']),
                Uuid::fromString($bookingData['booking_uuid']),
                $bookingData['free']
            );
        }

        return $result;
    }

    /**
     * @param UuidInterface $bookingUuid
     *
     * @return Identifiable|null
     * @throws \Assert\AssertionFailedException
     */
    public function findByBookingId(UuidInterface $bookingUuid) : ?Identifiable
    {
        $bookingData = $this->connection->fetchAssoc(
            'select id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free, booking_uuid from booking where booking_uuid = :id',
            ["id" => $bookingUuid]
        );

        if ($bookingData) {
            return new Booking(
                Uuid::fromString($bookingData['uuid']),
                $bookingData['idUser'],
                new \DateTimeImmutable($bookingData['from']),
                new \DateTimeImmutable($bookingData['to']),
                Uuid::fromString($bookingData['booking_uuid']),
                $bookingData['free']
            );
        }

        throw new ModelNotFound();
    }
}
