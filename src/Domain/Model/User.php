<?php
/**
 * Created by PhpStorm.
 * User: saverio
 * Date: 06/04/18
 * Time: 17.50
 */

namespace App\Domain\Model;


class User implements Model
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $phone;

    /**
     * User constructor.
     * @param int $id
     * @param string $email
     * @param string $phone
     */
    private function __construct(int $id, string $email, string $phone)
    {

        $this->id = $id;
        $this->email = $email;
        $this->phone = $phone;
    }

    public static function fromArray(array $userData) : User
    {
        return new self(
            $userData['id'],
            $userData['email'],
            $userData['phone']
        );
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
}