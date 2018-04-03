<?php
namespace App\Controller;

use App\Domain\Exception\ModelNotFound;
use App\Domain\Model\Booking;
use App\Domain\Repository\BookingRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BookingController
{
    /**
     * @param Request $request
     * @param BookingRepository $bookingRepository
     * @return JsonResponse
     */
    public function create(Request $request, BookingRepository $bookingRepository)
    {
        try {
            $bookingData = json_decode($request->getContent(), true);
            $booking = Booking::fromArray($bookingData);
            $bookingId = $bookingRepository->save($booking);
            $booking = $bookingRepository->find($bookingId);

            return new JsonResponse(["bookingId" => $booking->getId()], 201);
        } catch (ModelNotFound $e) {
            return new JsonResponse(["error" => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage(), "stack" => $e->getTraceAsString()], 400);
        }
    }

}