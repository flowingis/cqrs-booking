<?php
/**
 * Created by PhpStorm.
 * User: saverio
 * Date: 03/04/18
 * Time: 11.44
 */

namespace App\Domain\Model;


/**
 * Class Booking
 * @package App\Domain\Model
 */
/**
 * Class Booking
 * @package App\Domain\Model
 */
class Booking
{
    /**
     * @var int
     */
    private $idUser;
    /**
     * @var \DateTimeImmutable
     */
    private $from;
    /**
     * @var \DateTimeImmutable
     */
    private $to;

    /**
     * @var int;
     */
    private $id;

    /**
     * Booking constructor.
     * @param int $idUser
     * @param \DateTimeImmutable $from
     * @param \DateTimeImmutable $to
     * @param int $id
     */
    private function __construct(int $idUser, \DateTimeImmutable $from, \DateTimeImmutable $to, int $id = null)
    {
        $this->idUser = $idUser;
        $this->from = $from;
        $this->to = $to;
        $this->id = $id;
    }

    /**
     * @param array $bookingData
     * @return Booking
     * @throws \Exception
     */
    public static function fromArray(array $bookingData) : Booking
    {
        return new self(
            $bookingData['idUser'],
            new \DateTimeImmutable($bookingData['from']),
            new \DateTimeImmutable($bookingData['to']),
            $bookingData['id'] ?? null
        );
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getIdUser(): int
    {
        return $this->idUser;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getFrom(): \DateTimeImmutable
    {
        return $this->from;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getTo(): \DateTimeImmutable
    {
        return $this->to;
    }

    /**
     * @param Booking $booking
     * @return bool
     */
    public function isSlotAvailable(Booking $booking): bool
    {
        if ($this->getFrom()->getTimestamp() >= $booking->getTo()->getTimestamp())
        {
            return true;
        }

        if ($this->getTo()->getTimestamp() <= $booking->getFrom()->getTimestamp())
        {
            return true;
        }

        return false;
    }

}
