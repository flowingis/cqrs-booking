<?php

namespace App\Domain\Repository;

use Ramsey\Uuid\UuidInterface;
use Broadway\ReadModel\Identifiable;

interface Repository
{
    public function save(Identifiable $model) : void;
    public function find(UuidInterface $id) : ?Identifiable;
}
