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
     * Booking constructor.
     *
     * @param UuidInterface        $id
     * @param int                $idUser
     * @param \DateTimeImmutable $from
     * @param \DateTimeImmutable $to
     */
    public function __construct(
        UuidInterface $id,
        int $idUser,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to
    ) {
        $this->idUser = $idUser;
        $this->from = $from;
        $this->to = $to;
        $this->id = $id;
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
            $data['to']
        );
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'id'     => (string)$this->id,
            'idUser' => $this->idUser,
            'from'   => $this->from,
            'to'     => $this->to,
        ];
    }
}
