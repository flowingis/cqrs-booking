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

        $booking->assertSlotLengthIsValid();
        $booking->assertSlotLengthIsValid();
        $booking->assertTimeIsValid();

        $bookingOfDay = $this->bookingRepository->getBookingOfDay($booking->getFrom());
        foreach ($bookingOfDay as &$b) {
            $booking->assertSlotIsAvailable($b);
        }

        $bookingId = $this->bookingRepository->save($booking);
        $booking = $this->bookingRepository->find($bookingId);

        $this->mailer->send($to, 'Booked!');
        $this->sms->send($phone, 'Booked!');

        return $booking;
    }

}
