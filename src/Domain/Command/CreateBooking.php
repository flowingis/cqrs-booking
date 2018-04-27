<?php

namespace App\Domain\Command;

use Ramsey\Uuid\UuidInterface;

class CreateBooking
{
    private $userId;
    private $from;
    private $to;
    private $free;
    private $id;
    /**
     * @var UuidInterface
     */
    private $bookingUuid;

    /**
     * CreateBooking constructor.
     *
     * @param UuidInterface        $id
     * @param int                $userId
     * @param \DateTimeImmutable $from
     * @param \DateTimeImmutable $to
     * @param string             $free
     * @param UuidInterface      $bookingUuid
     */
    public function __construct(
        UuidInterface $id,
        int $userId,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        string $free,
        UuidInterface $bookingUuid
    )
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->from = $from;
        $this->to = $to;
        $this->free = $free;
        $this->bookingUuid = $bookingUuid;
    }

    /**
     * @return UuidInterface
     */
    public function getCourtId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
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
     * @return string
     */
    public function getFree(): string
    {
        return $this->free;
    }

    /**
     * @return UuidInterface
     */
    public function getBookingUuid(): UuidInterface
    {
        return $this->bookingUuid;
    }
}
