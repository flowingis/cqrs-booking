<?php
namespace App\Controller;

use App\Domain\Command\CreateBooking;
use App\Domain\Exception\ModelNotFound;
use App\Domain\Service\BookingCreator;
use App\Domain\ValueObject\AggregateId;
use Ramsey\Uuid\Uuid;
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
            $bookingData = json_decode($request->getContent(), true);
            $bookingId = new AggregateId(Uuid::uuid4());
            $booking = $bookingCreator->create(
                new CreateBooking(
                    $bookingId,
                    $bookingData['idUser'],
                    new \DateTimeImmutable($bookingData['from']),
                    new \DateTimeImmutable($bookingData['to']),
                    $bookingData['free']
                )
            );
            return new JsonResponse(["bookingId" => (string)$bookingId], 201);
        } catch (ModelNotFound $e) {
            return new JsonResponse(["error" => $e->getMessage()], 404);
        } catch (\DomainException $e) {
            return new JsonResponse(["message" => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage(), "stack" => $e->getTraceAsString()], 500);
        }
    }

}
