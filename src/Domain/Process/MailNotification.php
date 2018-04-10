<?php

namespace App\Domain\Process;


use App\Domain\Command\AssignPromotion;
use App\Domain\Event\BookingCreated;
use App\Domain\Repository\UserRepository;
use App\Service\Mailer;
use Broadway\Processor\Processor;

class MailNotification extends Processor
{
    /**
     * @var Mailer
     */
    private $mailer;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(Mailer $mailer, UserRepository $userRepository)
    {
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
    }

    public function handleBookingCreated(BookingCreated $event)
    {
        $user = $this->userRepository->find($event->getUserId());
        $this->mailer->send($user->getEmail(), 'Booked!');
    }
}
