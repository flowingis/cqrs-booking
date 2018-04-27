<?php

namespace App\Domain\Event;


use Broadway\Serializer\Serializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class PromotionAssigned implements Serializable
{
    /**
     * @var int
     */
    private $userId;
    /**
     * @var UuidInterface
     */
    private $bookingUuid;

    /**
     * PromotionAssigned constructor.
     *
     * @param int           $userId
     * @param UuidInterface $bookingUuid
     */
    public function __construct(int $userId, UuidInterface $bookingUuid)
    {
        $this->userId = $userId;
        $this->bookingUuid = $bookingUuid;
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

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new self($data['userId'], Uuid::fromString($data['bookingUuid']));
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'userId' => $this->userId,
            'bookingUuid' => (string)$this->bookingUuid
        ];
    }
}
