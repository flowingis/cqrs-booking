<?php

namespace App\Service;


class Mailer
{
    public function send(string $to, string $message): void
    {
        file_put_contents(__DIR__.'/../../tests/fixtures/notifications/mail.txt', $to . ' ### ' . $message);
    }
}
