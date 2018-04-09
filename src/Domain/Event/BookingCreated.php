<?php

namespace App\Domain\Event;


use App\Domain\ValueObject\AggregateId;

class BookingCreated
{
    /**
     * @var AggregateId
     */
    private $id;
    /**
     * @var int
     */
    private $userId;

    /**
     * BookingCreated constructor.
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
