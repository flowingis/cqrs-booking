<?php

namespace App\Domain\Event;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

use Broadway\Serializer\Serializable;

class BookingCreated implements Serializable
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
     * @var Uuid
     */
    private $bookingUuid;

    /**
     * BookingCreated constructor.
     *
     * @param UuidInterface        $id
     * @param int                $userId
     * @param string             $email
     * @param string             $phone
     * @param \DateTimeImmutable $from
     * @param \DateTimeImmutable $to
     * @param UuidInterface               $bookingUuid
     */
    public function __construct(
        UuidInterface $id,
        int $userId,
        string $email,
        string $phone,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        UuidInterface $bookingUuid
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->email = $email;
        $this->phone = $phone;
        $this->from = $from;
        $this->to = $to;
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

    /**
     * @return Uuid
     */
    public function getBookingUuid(): Uuid
    {
        return $this->bookingUuid;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new self(
            Uuid::fromString($data['id']),
            $data['userId'],
            $data['email'],
            $data['phone'],
            new \DateTimeImmutable($data['from']),
            new \DateTimeImmutable($data['to']),
            Uuid::fromString($data['bookingUuid'])
        );
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'id'          => (string)$this->id,
            'userId'      => $this->userId,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'from'        => $this->from->format('Y-m-d H:i'),
            'to'          => $this->to->format('Y-m-d H:i'),
            'bookingUuid' => (string)$this->bookingUuid,
        ];
    }
}
