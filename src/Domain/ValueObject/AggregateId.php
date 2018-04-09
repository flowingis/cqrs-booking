<?php

namespace App\Domain\ValueObject;

use Assert\Assertion;

class AggregateId
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * ProductId constructor.
     *
     * @param string $uuid
     *
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(string $uuid)
    {
        Assertion::uuid($uuid);
        $this->uuid = $uuid;
    }

    public function __toString()
    {
        return $this->uuid;
    }
}
