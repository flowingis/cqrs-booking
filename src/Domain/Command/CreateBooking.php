<?php

namespace App\Domain\Command;

use App\Domain\ValueObject\AggregateId;

class CreateBooking
{
    private $userId;
    private $from;
    private $to;
    private $free;
    private $id;

    /**
     * CreateBooking constructor.
     *
     * @param AggregateId        $userId
     * @param int                $id
     * @param \DateTimeImmutable $from
     * @param \DateTimeImmutable $to
     * @param string             $free
     */
    public function __construct(AggregateId $id, int $userId, \DateTimeImmutable $from, \DateTimeImmutable $to, string $free)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->from = $from;
        $this->to = $to;
        $this->free = $free;
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
}
