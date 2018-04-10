<?php
namespace App\Controller;

use App\Domain\Command\CreateBooking;
use App\Domain\Exception\ModelNotFound;
use App\Domain\Service\BookingCreator;
use Broadway\CommandHandling\CommandBus;
use Broadway\CommandHandling\SimpleCommandBus;
use Ramsey\Uuid\Uuid;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BookingController extends Controller
{
    /**
     * @param Request                              $request
     * @param LoggerInterface                      $logger
     *
     * @return JsonResponse
     * @throws \Assert\AssertionFailedException
     */
    public function create(Request $request, LoggerInterface $logger)
    {
        try {
            $bookingData = json_decode($request->getContent(), true);
            $bookingId = Uuid::uuid4();
            $commandBus = $this->get('broadway.command_handling.command_bus');

            $commandBus->dispatch(
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
        }
        catch (\Exception $e) {
            $logger->critical($e->getMessage() . ' #### ' . $e->getTraceAsString());
            return new JsonResponse(["error" => $e->getMessage(), "stack" => $e->getTraceAsString()], 500);
        }
    }

}
