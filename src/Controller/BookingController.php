<?php
namespace App\Controller;

use App\Domain\Exception\ModelNotFound;
use App\Domain\Service\BookingCreator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BookingController
{
    /**
     * @param Request $request
     * @param BookingCreator $bookingCreator
     * @return JsonResponse
     */
    public function create(Request $request, BookingCreator $bookingCreator)
    {
        try {
            $booking = $bookingCreator->create(json_decode($request->getContent(), true));

            return new JsonResponse(["bookingId" => $booking->getId()], 201);
        } catch (ModelNotFound $e) {
            return new JsonResponse(["error" => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage(), "stack" => $e->getTraceAsString()], 400);
        }
    }

}