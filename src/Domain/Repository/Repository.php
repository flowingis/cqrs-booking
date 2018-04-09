<?php
/**
 * Created by PhpStorm.
 * User: saverio
 * Date: 06/04/18
 * Time: 17.38
 */

namespace App\Domain\Repository;


use App\Domain\Model\Model;
use Ramsey\Uuid\UuidInterface;

interface Repository
{
    public function save(Model $model) : void;
    public function find(UuidInterface $id) : ?Model;
}
