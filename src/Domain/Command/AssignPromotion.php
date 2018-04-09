<?php

namespace App\Domain\Command;

use App\Domain\ValueObject\AggregateId;

class AssignPromotion
{
    private $userId;
    private $id;

    /**
     * CreateBooking constructor.
     *
     * @param AggregateId $id
     * @param int         $userId
     */
    public function __construct(AggregateId $id, int $userId)
    {
        $this->id = $id;
        $this->userId = $userId;
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
}
