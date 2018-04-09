<?php

namespace App\Domain\Command;

use Ramsey\Uuid\UuidInterface;

class AssignPromotion
{
    private $userId;
    private $id;

    /**
     * CreateBooking constructor.
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
