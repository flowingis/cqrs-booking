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

    public function __construct(UuidInterface $id, int $userId, \DateTimeImmutable $from, \DateTimeImmutable $to, string $free)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->from = $from;
        $this->to = $to;
        $this->free = $free;
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
}
