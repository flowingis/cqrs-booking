<?php

namespace App\Domain\Service;

use App\Domain\Model\Booking;
use App\Domain\Repository\BookingRepository;
use App\Domain\Repository\UserRepository;
use App\Service\Mailer;
use App\Service\Sms;

/**
 * Class BookingCreator
 * @package App\Domain\Service
 */
class BookingCreator
{
    /**
     * @var BookingRepository
     */
    private $bookingRepository;
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
     * BookingCreator constructor.
     * @param BookingRepository $bookingRepository
     * @param UserRepository $userRepository
     * @param Mailer $mailer
     * @param Sms $sms
     */
    public function __construct(
        BookingRepository $bookingRepository,
        UserRepository $userRepository,
        Mailer $mailer,
        Sms $sms
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->mailer = $mailer;
        $this->sms = $sms;
        $this->userRepository = $userRepository;
    }

    /**
     * @param array $bookingData
     * @return Booking
     * @throws \Exception
     */
    public function create(array $bookingData) : Booking
    {
        // booking creation
        $booking = Booking::fromArray($bookingData);

        $booking->assertSlotLengthIsValid();
        $booking->assertTimeIsValid();

        $bookingOfDay = $this->bookingRepository->findBookingByDay($booking->getFrom());
        foreach ($bookingOfDay as &$b) {
            $booking->assertSlotIsAvailable($b);
        }

        $bookingId = $this->bookingRepository->save($booking);
        // end booking creation

        //booking promotion
        $booking = $this->bookingRepository->find($bookingId);
        if (count($this->bookingRepository->findAllByUser($booking->getIdUser())) === 10) {
            $booking->free();
            $this->bookingRepository->save($booking);
        }
        // end booking promotion

        // booking notification
        $user = $this->userRepository->find($booking->getIdUser());

        $this->mailer->send($user->getEmail(), 'Booked!');
        $this->sms->send($user->getPhone(), 'Booked!');
        // booking notification

        return $booking;
    }

}
