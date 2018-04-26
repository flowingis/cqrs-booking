<?php

namespace App\Domain\Event;

use Ramsey\Uuid\UuidInterface;

class BookingCreated
{
    /**
     * @var UuidInterface
     */
    private $id;
    /**
     * @var int
     */
    private $userId;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $phone;
    /**
     * @var \DateTimeImmutable
     */
    private $from;
    /**
     * @var \DateTimeImmutable
     */
    private $to;

    /**
     * BookingCreated constructor.
     *
     * @param UuidInterface        $id
     * @param int                $userId
     * @param string             $email
     * @param string             $phone
     * @param \DateTimeImmutable $from
     * @param \DateTimeImmutable $to
     */
    public function __construct(
        UuidInterface $id,
        int $userId,
        string $email,
        string $phone,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->email = $email;
        $this->phone = $phone;
        $this->from = $from;
        $this->to = $to;
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
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
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
}
