<?php

namespace App\Domain\Command;

use Ramsey\Uuid\UuidInterface;

class AssignPromotion
{
    private $userId;
    private $id;
    /**
     * @var UuidInterface
     */
    private $bookingUuid;

    /**
     * CreateBooking constructor.
     *
     * @param UuidInterface   $id
     * @param int           $userId
     * @param UuidInterface $bookingUuid
     */
    public function __construct(UuidInterface $id, int $userId, UuidInterface $bookingUuid)
    {
        $this->id = $id;
        $this->userId = $userId;
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
     * @return UuidInterface
     */
    public function getBookingUuid(): UuidInterface
    {
        return $this->bookingUuid;
    }
}
