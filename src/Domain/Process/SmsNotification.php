<?php

namespace App\Domain\Process;


use App\Domain\Event\BookingCreated;
use App\Domain\Repository\UserRepository;
use App\Service\Sms;
use Broadway\Processor\Processor;

class SmsNotification extends Processor
{
    /**
     * @var Sms
     */
    private $sms;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(Sms $sms, UserRepository $userRepository)
    {
        $this->sms = $sms;
        $this->userRepository = $userRepository;
    }

    public function handleBookingCreated(BookingCreated $event)
    {
        $user = $this->userRepository->find($event->getUserId());
        $this->sms->send($user->getPhone(), 'Booked!');
    }
}
