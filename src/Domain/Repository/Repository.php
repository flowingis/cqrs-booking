<?php
/**
 * Created by PhpStorm.
 * User: saverio
 * Date: 06/04/18
 * Time: 17.38
 */

namespace App\Domain\Repository;


use App\Domain\Model\Model;
use App\Domain\ValueObject\AggregateId;

interface Repository
{
    public function save(Model $model) : void;
    public function find(AggregateId $id) : ?Model;
}
