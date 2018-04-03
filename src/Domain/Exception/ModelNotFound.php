<?php
/**
 * Created by PhpStorm.
 * User: saverio
 * Date: 03/04/18
 * Time: 12.44
 */

namespace App\Domain\Exception;


use Throwable;

class ModelNotFound extends \DomainException
{
    public function __construct(string $message = "Model not found", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}