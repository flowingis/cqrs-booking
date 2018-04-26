<?php

namespace App\Domain\Repository;


use App\Domain\Exception\ModelNotFound;
use App\Domain\Model\Booking;
use App\Domain\Model\Model;
use App\Domain\ReadModel\BookingBackoffice;
use Broadway\ReadModel\Identifiable;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * Class BookingRepository
 * @package App\Domain\Repository
 */
class BookingBackofficeRepository implements Repository
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
     * @param Model $bookingBackoffice
     */
    public function save(Identifiable $bookingBackoffice): void
    {
        $this->connection->insert('booking_backoffice', [
            "uuid" => (string)$bookingBackoffice->getId(),
            "id_user" => $bookingBackoffice->getUserId(),
            "date_from" => $bookingBackoffice->getFrom()->format('Y-m-d H:i'),
            "date_to" => $bookingBackoffice->getTo()->format('Y-m-d H:i'),
            "email" => $bookingBackoffice->getEmail(),
            "phone" => $bookingBackoffice->getPhone()
        ]);
    }

    public function find(UuidInterface $id): ?Identifiable
    {
        // TODO: Implement find() method.
    }
}
