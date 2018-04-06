<?php
/**
 * Created by PhpStorm.
 * User: saverio
 * Date: 06/04/18
 * Time: 9.49
 */

namespace App\Domain\Exception;

use Throwable;

class SlotNotAvailable extends \DomainException
{
    public function __construct(string $message = "Slot not available", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}