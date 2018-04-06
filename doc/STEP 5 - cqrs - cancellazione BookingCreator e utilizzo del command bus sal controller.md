STEP 5 - cqrs - cancellazione BookingCreator e utilizzo del command bus sal controller
===========================================

- Verifica funzionamento dopo implementazione: `docker-compose exec php ./idephix.phar build`

### FILE DA MODIFICARE:

- Cancellare file src/Domain/Service/BookingCreator.php

- Modificare services.yaml:

```
-    App\Domain\Service\BookingCreator:
-        class: App\Domain\Service\BookingCreator
-        arguments:
-          - '@App\Domain\Repository\BookingRepository'
-          - '@broadway.command_handling.command_bus'
-          - '@App\Domain\Repository\UserRepository'
-          - '@App\Service\Mailer'
-          - '@App\Service\Sms'
``

- Modificare BookingController:

```
+use Broadway\CommandHandling\CommandBus;
+use Broadway\CommandHandling\SimpleCommandBus;
 use Ramsey\Uuid\Uuid;
 use Psr\Log\LoggerInterface;
+use Symfony\Bundle\FrameworkBundle\Controller\Controller;
 use Symfony\Component\HttpFoundation\JsonResponse;
 use Symfony\Component\HttpFoundation\Request;
 
-class BookingController
+class BookingController extends Controller
 {
     /**
-     * @param Request $request
-     * @param BookingCreator $bookingCreator
+     * @param Request                              $request
+     * @param LoggerInterface                      $logger
+     *
      * @return JsonResponse
+     * @throws \Assert\AssertionFailedException
      */
-    public function create(Request $request, BookingCreator $bookingCreator, LoggerInterface $logger)
+    public function create(Request $request, LoggerInterface $logger)
     {
         try {
             $bookingData = json_decode($request->getContent(), true);
             $bookingId = Uuid::fromString(Uuid::uuid4());
-            $booking = $bookingCreator->create(
+            $commandBus = $this->get('broadway.command_handling.command_bus');
+
+            $commandBus->dispatch(
                 new CreateBooking(
                     $bookingId,
                     $bookingData['idUser'],
```

