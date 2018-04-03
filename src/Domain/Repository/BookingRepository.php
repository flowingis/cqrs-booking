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
use Doctrine\DBAL\Connection;

/**
 * Class BookingRepository
 * @package App\Domain\Repository
 */
class BookingRepository
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
    public function save(Booking $booking) : int
    {
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
    public function find(int $id) : ?Booking
    {
        $bookingData = $this->connection->fetchAssoc(
            'select id, id_user as idUser, date_from as `from`, date_to as `to` from booking where id = :id',
            ["id" => $id]
        );

        if ($bookingData) {
            return Booking::fromArray($bookingData);
        }

        throw new ModelNotFound();
    }


}