<?php

namespace App\Domain\Service;

use App\Domain\Exception\SlotLengthInvalid;
use App\Domain\Exception\SlotNotAvailable;
use App\Domain\Exception\SlotTimeInvalid;
use App\Domain\Model\Booking;
use App\Domain\Repository\BookingRepository;
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
     * BookingCreator constructor.
     *
     * @param BookingRepository $bookingRepository
     * @param Mailer            $mailer
     * @param Sms               $sms
     */
    public function __construct(BookingRepository $bookingRepository, Mailer $mailer, Sms $sms)
    {
        $this->bookingRepository = $bookingRepository;
        $this->mailer = $mailer;
        $this->sms = $sms;
    }

    /**
     * @param array $bookingData
     * @return Booking
     * @throws \Exception
     */
    public function create(array $bookingData) : Booking
    {
        $booking = Booking::fromArray($bookingData);

        if (!Booking::isSlotLengthValid($booking)) {
            throw new SlotLengthInvalid();
        }

        if (!Booking::isTimeValid($booking)) {
            throw new SlotTimeInvalid();
        }

        $bookingOfDay = $this->bookingRepository->getBookingOfDay($booking->getFrom());
        foreach ($bookingOfDay as &$b) {
            if (!$booking->isSlotAvailable($b)) {
                throw new SlotNotAvailable();
            }
        }

        $bookingId = $this->bookingRepository->save($booking);
        $booking = $this->bookingRepository->find($bookingId);

        $this->mailer->send($to, 'Booked!');
        $this->sms->send($phone, 'Booked!');

        return $booking;
    }

}
