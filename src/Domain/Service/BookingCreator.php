<?php

namespace App\Domain\Service;

use App\Domain\Command\CreateBooking;
use App\Domain\Model\Booking;
use App\Domain\Repository\BookingRepository;
use App\Domain\Repository\UserRepository;
use App\Service\Mailer;
use App\Service\Sms;
use Broadway\CommandHandling\CommandBus;

/**
 * Class BookingCreator
 * @package App\Domain\Service
 */
class BookingCreator
{
    private $commandBus;
    /**
     * @var Mailer
     */
    private $mailer;
    /**
     * @var Sms
     */
    private $sms;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var BookingRepository
     */
    private $bookingRepository;

    /**
     * BookingCreator constructor.
     *
     * @param BookingRepository $bookingRepository
     * @param CommandBus        $commandBus
     * @param UserRepository    $userRepository
     * @param Mailer            $mailer
     * @param Sms               $sms
     */
    public function __construct(
        BookingRepository $bookingRepository,
        CommandBus $commandBus,
        UserRepository $userRepository,
        Mailer $mailer,
        Sms $sms
    ) {
        $this->commandBus = $commandBus;
        $this->mailer = $mailer;
        $this->sms = $sms;
        $this->userRepository = $userRepository;
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * @param CreateBooking $bookingData
     * @return Booking
     * @throws \Exception
     */
    public function create(CreateBooking $createBooking)
    {
        // booking creation
        $this->commandBus->dispatch($createBooking);
        // end booking creation

        //booking promotion
//        $booking = $this->bookingRepository->find(($createBooking->getId()));
//        if (count($this->bookingRepository->findAllByUser($createBooking->getUserId())) === 10) {
//            $booking->free();
//            $this->bookingRepository->save($booking);
//        }
        // end booking promotion

        // booking notification
//        $user = $this->userRepository->find($booking->getIdUser());
//
//        $this->mailer->send($user->getEmail(), 'Booked!');
//        $this->sms->send($user->getPhone(), 'Booked!');
        // booking notification

//        return $booking;
    }

}
