STEP 3 - cqrs - aggiungere comando per assegnare la promozione
===========================================

- Stato iniziale: lanciare `docker-compose exec php ./idephix.phar build` : tutti test verdi e uno skipped

- Verifica funzionamento dopo implementazione: `docker-compose exec php ./idephix.phar build` tutti i test verdi

### FILE DA MODIFICARE:

- Modificare BookingControllerTest:

```
    public function it_should_be_free_booking_when_booking_is_the_tenth()
    {
-       $this->markTestSkipped('To fix');
```

- Creare comando AssignPromotion:

```
<?php
+
+namespace App\Domain\Command;
+
+use Ramsey\Uuid\UuidInterface;;
+
+class AssignPromotion
+{
+    private $userId;
+    private $id;
+
+    /**
+     * CreateBooking constructor.
+     *
+     * @param UuidInterface $id
+     * @param int         $userId
+     */
+    public function __construct(UuidInterface $id, int $userId)
+    {
+        $this->id = $id;
+        $this->userId = $userId;
+    }
+
+    /**
+     * @return UuidInterface
+     */
+    public function getId(): UuidInterface
+    {
+        return $this->id;
+    }
+
+    /**
+     * @return int
+     */
+    public function getUserId(): int
+    {
+        return $this->userId;
+    }
+}
```

- Modificare BookingCommandHandler:

```
 namespace App\Domain;
 
 
+use App\Domain\Command\AssignPromotion;
 use App\Domain\Command\CreateBooking;
+use App\Domain\Event\BookingCreated;
 use App\Domain\Model\Booking;
 use App\Domain\Repository\BookingRepository;
 use App\Domain\Repository\Repository;
 use Broadway\CommandHandling\SimpleCommandHandler;
+use Broadway\Domain\DomainEventStream;
+use Broadway\Domain\DomainMessage;
+use Broadway\Domain\Metadata;
+use Broadway\EventHandling\EventBus;
 
 class BookingCommandHandler extends SimpleCommandHandler
 {
     /**
      * @var BookingRepository
      */
     private $bookingRepository;
+    /**
+     * @var EventBus
+     */
+    private $eventBus;
 
     /**
      * BookingCommandHandler constructor.
      *
      * @param Repository $repository
+     * @param EventBus   $eventBus
      */
-    public function __construct(Repository $repository)
+    public function __construct(Repository $repository, EventBus $eventBus)
     {
         $this->bookingRepository = $repository;
+        $this->eventBus = $eventBus;
     }
 
     /**
@@ -45,5 +57,28 @@ public function handleCreateBooking(CreateBooking $command)
         }
 
         $this->bookingRepository->save($booking);
+
+        $this->eventBus->publish(
+            new DomainEventStream(
+                [
+                    DomainMessage::recordNow(
+                        $command->getId(),
+                        0,
+                        new Metadata([]),
+                        new BookingCreated($command->getId(), $command->getUserId())
+                    )
+                ]
+            )
+        );
+    }
+
+    public function handleAssignPromotion(AssignPromotion $command)
+    {
+        $booking = $this->bookingRepository->find($command->getId());
+
+        if (count($this->bookingRepository->findAllByUser($command->getUserId())) === 10) {
+            $booking->free();
+            $this->bookingRepository->update($booking);
+        }
     }
 }
```

- Creare evento BookingCreated: 

```
+<?php
+
+namespace App\Domain\Event;
+
+
+use Ramsey\Uuid\UuidInterface;;
+
+class BookingCreated
+{
+    /**
+     * @var UuidInterface
+     */
+    private $id;
+    /**
+     * @var int
+     */
+    private $userId;
+
+    /**
+     * BookingCreated constructor.
+     *
+     * @param UuidInterface $id
+     * @param int         $userId
+     */
+    public function __construct(UuidInterface $id, int $userId)
+    {
+        $this->id = $id;
+        $this->userId = $userId;
+    }
+
+    /**
+     * @return UuidInterface
+     */
+    public function getId(): UuidInterface
+    {
+        return $this->id;
+    }
+
+    /**
+     * @return int
+     */
+    public function getUserId(): int
+    {
+        return $this->userId;
+    }
+}
```

- Creare processor PromotionAssignment:

```
+<?php
+
+namespace App\Domain\Process;
+
+
+use App\Domain\Command\AssignPromotion;
+use App\Domain\Event\BookingCreated;
+use Broadway\CommandHandling\CommandBus;
+use Broadway\Processor\Processor;
+
+class PromotionAssignment extends Processor
+{
+    /**
+     * @var CommandBus
+     */
+    private $commandBus;
+
+    public function __construct(CommandBus $commandBus)
+    {
+        $this->commandBus = $commandBus;
+    }
+
+    public function handleBookingCreated(BookingCreated $event)
+    {
+        $this->commandBus->dispatch(
+            new AssignPromotion($event->getId(), $event->getUserId())
+        );
+    }
+}
```

- Modificare services.yaml:

```
     App\Domain\BookingCommandHandler:
         class: App\Domain\BookingCommandHandler
-        arguments: ['@App\Domain\Repository\BookingRepository']
+        arguments:
+          - '@App\Domain\Repository\BookingRepository'
+          - '@broadway.event_handling.event_bus'
         tags:
             - { name: broadway.command_handler }
 
+    App\Domain\Process\AssignPromotion:
+            class: App\Domain\Process\PromotionAssignment
+            arguments: ['@broadway.command_handling.command_bus']
+            tags:
+                - { name: broadway.domain.event_listener }
+
```

- Modificare BookingRepository::

```
+    /**
+     * @param Model $booking
+     */
+    public function update(Model $booking): void
+    {
+        $this->connection->update(
+            'booking',
+            ["free" => $booking->isFree()],
+            ["uuid" => (string)$booking->getId()]
+        );
+    }
+
     /**
      * @param UuidInterface $id
      * @return Booking|null
@@ -96,7 +108,7 @@ public function findBookingByDay(\DateTimeImmutable $day) : array
     public function findAllByUser(int $userId) : array
     {
         $bookingsData = $this->connection->executeQuery(
-            'SELECT id, id_user as idUser, date_from as `from`, date_to as `to`, free FROM booking WHERE id_user=:id ORDER BY id ASC',
+            'SELECT id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free FROM booking WHERE id_user=:id ORDER BY id ASC',
             ["id" => $userId]);
```

- Modificare BookingCreator:

```
     public function create(CreateBooking $createBooking) : Booking
     {
-        // booking creation
         $this->commandBus->dispatch($createBooking);
-        // end booking creation
 
-        //booking promotion
//         $booking = $this->bookingRepository->find(($createBooking->getId()));
``
