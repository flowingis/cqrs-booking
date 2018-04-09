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

use App\Domain\Command\CreateBooking;
use App\Domain\Exception\SlotLengthInvalid;
use App\Domain\Exception\SlotNotAvailable;
use App\Domain\Exception\SlotTimeInvalid;
use App\Domain\ValueObject\AggregateId;

/**
 * Class Booking
 * @package App\Domain\Model
 */
class Booking implements Model
{
    const ONE_HOUR_TIMESTAMP = 1 * 60 * 60;
    const THREE_HOURS_TIMESTAMP = 3 * 60 * 60;
    const FIRST_HOUR_BOOKABLE = 9;
    const LAST_HOUR_BOOKABLE = 23;

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
     * @var AggregateId;
     */
    private $id;
    /**
     * @var bool
     */
    private $free;

    /**
     * Booking constructor.
     * @param int $idUser
     * @param \DateTimeImmutable $from
     * @param \DateTimeImmutable $to
     * @param bool $free
     */
    private function __construct(
        AggregateId $id,
        int $idUser,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        bool $free
    ) {
        $this->idUser = $idUser;
        $this->from = $from;
        $this->to = $to;
        $this->id = $id;
        $this->free = $free;
    }

    /**
     * @param array $bookingData
     * @return Booking
     * @throws \Exception
     */
    public static function fromArray(array $bookingData) : Booking
    {
        return new self(
            new AggregateId($bookingData['uuid']),
            $bookingData['idUser'],
            new \DateTimeImmutable($bookingData['from']),
            new \DateTimeImmutable($bookingData['to']),
            $bookingData['free']
        );
    }

    public static function fromCommand(CreateBooking $createBooking) : Booking
    {
        return new self(
            $createBooking->getId(),
            $createBooking->getUserId(),
            $createBooking->getFrom(),
            $createBooking->getTo(),
            $createBooking->getFree()
        );
    }

    /**
     * @return AggregateId
     */
    public function getId(): AggregateId
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

    public function free() : void
    {
        $this->free = true;
    }

    /**
     * @param Booking $booking
     * @return bool
     */
    public function assertSlotIsAvailable(Booking $booking): bool
    {
        if ($this->getFrom()->getTimestamp() >= $booking->getTo()->getTimestamp())
        {
            return true;
        }

        if ($this->getTo()->getTimestamp() <= $booking->getFrom()->getTimestamp())
        {
            return true;
        }

        throw new SlotNotAvailable();
    }

    /**
     * @return bool
     */
    public function assertSlotLengthIsValid(): bool
    {
        $diff = $this->getTo()->getTimestamp() - ($this->getFrom()->getTimestamp());

        if ($diff < self::ONE_HOUR_TIMESTAMP) {
            throw new SlotLengthInvalid();
        }

        if ($diff > self::THREE_HOURS_TIMESTAMP) {
            throw new SlotLengthInvalid();
        }

        return true;
    }


    /**
     * @return bool
     */
    public function assertTimeIsValid(): bool
    {
        $fromHour = intval($this->getFrom()->format('H'), 10);
        $fromMinute = intval($this->getFrom()->format('i'), 10);
        $toHour = intval($this->getTo()->format('H'), 10);
        $toMinute = intval($this->getTo()->format('i'), 10);

        if (self::isHourValid($fromHour, $fromMinute) and self::isHourValid($toHour, $toMinute)) {
            return true;
        }

        throw new SlotTimeInvalid();
    }

    /**
     * @param int $hour
     * @param int $minute
     * @return bool
     */
    private static function isHourValid(int $hour, int $minute): bool
    {
        if ($hour < self::FIRST_HOUR_BOOKABLE) {
            return false;
        }

        if ($hour > self::LAST_HOUR_BOOKABLE or ($hour == self::LAST_HOUR_BOOKABLE and $minute > 0)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isFree(): bool
    {
        return $this->free;
    }

}
