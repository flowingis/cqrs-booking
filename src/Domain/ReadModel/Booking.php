<?php

namespace App\Domain\ReadModel;


use Broadway\ReadModel\Identifiable;
use Broadway\ReadModel\SerializableReadModel;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class Booking
 * @package App\Domain\Model
 */
class Booking implements Identifiable, SerializableReadModel
{
    /**
     * @var int
     */
    private $idUser;
    /**
     * @var \DateTimeImmutable
     */
    private $from;
    /**
     * @var \DateTimeImmutable
     */
    private $to;
    /**
     * @var UuidInterface;
     */
    private $id;
    /**
     * @var bool
     */
    private $free;
    /**
     * @var UuidInterface
     */
    private $bookingUuid;

    /**
     * Booking constructor.
     *
     * @param UuidInterface        $id
     * @param int                $idUser
     * @param \DateTimeImmutable $from
     * @param \DateTimeImmutable $to
     * @param UuidInterface      $bookingUuid
     * @param bool               $free
     */
    public function __construct(
        UuidInterface $id,
        int $idUser,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        UuidInterface $bookingUuid,
        bool $free = false
    ) {
        $this->idUser = $idUser;
        $this->from = $from;
        $this->to = $to;
        $this->id = $id;
        $this->bookingUuid = $bookingUuid;
        $this->free = $free;
    }


    /**
     * @return int
     */
    public function getIdUser(): int
    {
        return $this->idUser;
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
    public function getId(): string
    {
        return (string)$this->id;
    }

    /**
     * @return mixed The object instance
     */
    public static function deserialize(array $data)
    {
        return new self(
            Uuid::fromString($data['id']),
            $data['idUser'],
            $data['from'],
            $data['to'],
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
            'idUser'      => $this->idUser,
            'from'        => $this->from,
            'to'          => $this->to,
            'bookingUuid' => (string)$this->bookingUuid,
        ];
    }

    /**
     * @return bool
     */
    public function isFree(): bool
    {
        return $this->free;
    }

    /**
     * @return UuidInterface
     */
    public function getBookingUuid(): UuidInterface
    {
        return $this->bookingUuid;
    }

    public function free()
    {
        $this->free = true;
    }
}
