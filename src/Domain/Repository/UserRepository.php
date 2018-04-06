<?php
/**
 * Created by PhpStorm.
 * User: saverio
 * Date: 06/04/18
 * Time: 17.37
 */

namespace App\Domain\Repository;


use App\Domain\Exception\ModelNotFound;
use App\Domain\Model\Model;
use App\Domain\Model\User;
use Doctrine\DBAL\Driver\Connection;

/**
 * Class UserRepository
 * @package App\Domain\Repository
 */
class UserRepository implements Repository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * BookingRepository constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Model $user
     * @return int
     */
    public function save(Model $user): int
    {
        // TODO: Implement save() method.
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function find(int $id): ?Model
    {
        $userData = $this->connection->fetchAssoc(
            'select * from user where id = :id',
            ["id" => $id]
        );

        if ($userData) {
            return User::fromArray($userData);
        }

        throw new ModelNotFound();
    }
}