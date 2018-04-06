<?php
/**
 * Created by PhpStorm.
 * User: saverio
 * Date: 03/04/18
 * Time: 18.10
 */

namespace App\Domain\Service;

use App\Domain\Exception\SlotLengthInvalid;
use App\Domain\Exception\SlotNotAvailable;
use App\Domain\Model\Booking;
use App\Domain\Repository\BookingRepository;

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
     * BookingCreator constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
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

        $bookingOfDay = $this->bookingRepository->getBookingOfDay($booking->getFrom());
        foreach ($bookingOfDay as &$b) {
            if (!$booking->isSlotAvailable($b)) {
                throw new SlotNotAvailable();
            }
        }

        $bookingId = $this->bookingRepository->save($booking);
        $booking = $this->bookingRepository->find($bookingId);

        return $booking;
    }

}