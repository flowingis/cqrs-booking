<?php
/**
 * Created by PhpStorm.
 * User: saverio
 * Date: 06/04/18
 * Time: 17.38
 */

namespace App\Domain\Repository;


use App\Domain\Model\Model;

interface Repository
{
    public function save(Model $model) : int;
    public function find(int $id) : ?Model;
}