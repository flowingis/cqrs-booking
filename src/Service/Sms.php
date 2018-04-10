<?php

namespace App\Service;


class Sms
{
    public function send(string $phone, string $message): void
    {
        file_put_contents(__DIR__.'/../../tests/fixtures/notifications/sms.txt', $phone . ' ### ' . $message);
    }
}
