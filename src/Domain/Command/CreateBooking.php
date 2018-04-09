<?php

namespace App\Domain\Command;


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
     * @param int                $userId
     * @param \DateTimeImmutable $from
     * @param \DateTimeImmutable $to
     * @param string             $free
     * @param int|null           $id
     */
    public function __construct(int $userId, int $id, \DateTimeImmutable $from, \DateTimeImmutable $to, string $free)
    {
        $this->userId = $userId;
        $this->from = $from;
        $this->to = $to;
        $this->free = $free;
        $this->id = $id;
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
     * @return int|null
     */
    public function getId(): int
    {
        return $this->id;
    }
}
