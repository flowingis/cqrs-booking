<?php
/**
 * Created by PhpStorm.
 * User: saverio
 * Date: 03/04/18
 * Time: 11.59
 */

namespace App\Domain\Repository;


use App\Domain\Exception\ModelNotFound;
use App\Domain\Model\Booking;
use App\Domain\Model\Model;
use App\Domain\ValueObject\AggregateId;
use Doctrine\DBAL\Connection;
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
     * @param Model $booking
     */
    public function save(Model $booking): void
    {
        $this->connection->insert('booking', [
            "uuid" => (string)$booking->getId(),
            "id_user" => $booking->getIdUser(),
            "date_from" => $booking->getFrom()->format('Y-m-d H:i'),
            "date_to" => $booking->getTo()->format('Y-m-d H:i'),
        ]);
    }

    /**
     * @param AggregateId $id
     * @return Booking|null
     * @throws \Exception
     */
    public function find(AggregateId $id) : ?Model
    {
        $bookingData = $this->connection->fetchAssoc(
            'select id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free from booking where uuid = :id',
            ["id" => $id]
        );

        if ($bookingData) {
            return Booking::fromArray($bookingData);
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
            'SELECT id, id_user as idUser, date_from as `from`, date_to as `to`, free FROM booking WHERE id_user=:id ORDER BY id ASC',
            ["id" => $userId]);

        $result = array();

        foreach ($bookingsData->fetchAll() as &$bookingData) {
            $result[] = Booking::fromArray($bookingData);
        }

        return $result;
    }

}
