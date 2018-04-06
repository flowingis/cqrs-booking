<?php
/**
 * Created by PhpStorm.
 * User: saverio
 * Date: 06/04/18
 * Time: 9.49
 */

namespace App\Domain\Exception;

use Throwable;

class SlotLengthInvalid extends \DomainException
{
    public function __construct(string $message = "Slot must be length min 1 hour and max 3 hours", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}