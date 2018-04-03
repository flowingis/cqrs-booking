<?php
/**
 * Created by PhpStorm.
 * User: saverio
 * Date: 03/04/18
 * Time: 18.10
 */

namespace App\Domain\Service;


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
        $bookingId = $this->bookingRepository->save($booking);
        $booking = $this->bookingRepository->find($bookingId);

        return $booking;
    }

}