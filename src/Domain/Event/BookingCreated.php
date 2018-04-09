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
     * BookingCreated constructor.
     *
     * @param UuidInterface $id
     * @param int         $userId
     */
    public function __construct(UuidInterface $id, int $userId)
    {
        $this->id = $id;
        $this->userId = $userId;
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
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
}
