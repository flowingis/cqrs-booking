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
     * @param Booking $booking
     * @return int
     */
    public function save(Model $booking) : int
    {
        if ($booking->getId()) {
            $this->connection->update('booking', [
                "date_from" => $booking->getFrom()->format('Y-m-d H:i'),
                "date_to" => $booking->getTo()->format('Y-m-d H:i'),
                "free" => $booking->isFree()
            ],
            ["id" => $booking->getId()]);

            return $booking->getId();
        }

        $this->connection->insert('booking', [
            "id_user" => $booking->getIdUser(),
            "date_from" => $booking->getFrom()->format('Y-m-d H:i'),
            "date_to" => $booking->getTo()->format('Y-m-d H:i'),
        ]);

        return $this->connection->lastInsertId();
    }

    /**
     * @param int $id
     * @return Booking|null
     * @throws \Exception
     */
    public function find(int $id) : ?Model
    {
        $bookingData = $this->connection->fetchAssoc(
            'select id, id_user as idUser, date_from as `from`, date_to as `to`, free from booking where id = :id',
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
            'SELECT id, id_user as idUser, date_from as `from`, date_to as `to`, free FROM booking WHERE DATE(date_from)=:date',
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
            'SELECT id, id_user as idUser, date_from as `from`, date_to as `to`, free FROM booking WHERE id_user=:id',
            ["id" => $userId]);

        $result = array();

        foreach ($bookingsData->fetchAll() as &$bookingData) {
            $result[] = Booking::fromArray($bookingData);
        }

        return $result;
    }

}