STEP 8.3.1 - event soucing - test verde per la creazione di una prenotazione con slot occupato
===================================================

- Stato iniziale: lanciare `docker-compose exec php ./idephix.phar build` : 

```
PHPUnit 6.5.7 by Sebastian Bergmann and contributors.

Testing Project Test Suite
SSSSSSSSSSSS.F.........                                           23 / 23 (100%)

Time: 781 ms, Memory: 6.00MB

There was 1 failure:

1) App\Tests\Domain\Command\CreateBookingTest::should_not_create_booking_for_not_available_slots
Failed asserting that exception of type "\App\Domain\Exception\SlotNotAvailable" is thrown.

FAILURES!
Tests: 23, Assertions: 16, Failures: 1, Skipped: 12.
```

- Verifica funzionamento dopo implementazione: `docker-compose exec php ./idephix.phar build`: tutti i test verdi a parte 12 skipped

### FILE DA MODIFICARE:


- Aggiungere a \App\Tests\Domain\Command\CreateBookingTest:

```
    @@ -103,6 +103,7 @@ public function should_not_create_booking_for_not_available_slots()
         ]));
 
         $this->scenario
+            ->withAggregateId($courtId)
             ->given([$bookingCreated])
             ->when($createBooking);
     }
```

- Modificare \App\Domain\BookingCommandHandler:

```
@@ -43,8 +43,14 @@ public function handleCreateBooking(CreateBooking $command)
     {
         $user = $this->userRepository->find($command->getUserId());
 
-        $courtAggregate = new Court();
-        $courtAggregate->createBooking($command, $user);
+        /** @var Court $courtAggregate */
+        try {
+            $courtAggregate = $this->courtAggregateRepository->load($command->getCourtId());
+            $courtAggregate->createBooking($command, $user);
+        } catch (\Broadway\Repository\AggregateNotFoundException $exception) {
+            $courtAggregate = new Court();
+            $courtAggregate->createBooking($command, $user);
+        }
 
         $this->courtAggregateRepository->save($courtAggregate);
 //        $booking = Booking::fromCommand($command);
```

- Rinomanare metodo in \App\Domain\Event\BookingCreated:

```
    @@ -61,7 +61,7 @@ public function __construct(
     /**
      * @return UuidInterface
      */
-    public function getId(): UuidInterface
+    public function getCourtId(): UuidInterface
     {
         return $this->id;
     }
```

- Aggiungere a \App\Domain\Aggregate\Court:

```
+use App\Domain\Exception\SlotNotAvailable;
+use App\Domain\Model\Booking;
 use App\Domain\Model\User;
 use Broadway\EventSourcing\EventSourcedAggregateRoot;
 
 class Court extends EventSourcedAggregateRoot
 {
+    /**
+     * @var Booking[]
+     */
+    private $bookings = [];
+
     public function createBooking(CreateBooking $command, User $user)
     {
+        $this->assertSlotIsAvailable($command);
+
         $this->apply(
             new BookingCreated(
                 $command->getCourtId(),
@@ -23,6 +32,40 @@ public function createBooking(CreateBooking $command, User $user)
         );
     }
 
+    /**
+     * @param CreateBooking $createBooking
+     */
+    private function assertSlotIsAvailable(CreateBooking $createBooking)
+    {
+        /** @var Booking $booking */
+        foreach ($this->bookings as $booking) {
+            if ($booking->getFrom()->getTimestamp() >= $createBooking->getTo()->getTimestamp())
+            {
+                continue;
+            }
+
+            if ($booking->getTo()->getTimestamp() <= $createBooking->getFrom()->getTimestamp())
+            {
+                continue;
+            }
+
+            throw new SlotNotAvailable();
+        }
+    }
+
+    protected function applyBookingCreated(BookingCreated $event)
+    {
+        $this->bookings[] = Booking::fromArray(
+            [
+                'uuid' => $event->getCourtId(),
+                'idUser' => $event->getUserId(),
+                'from' => $event->getFrom()->format('Y-m-d H:i'),
+                'to' => $event->getTo()->format('Y-m-d H:i'),
+                'free' => false
+            ]
+        );
+    }
+
```

- Modificare \App\Domain\Projector\BookingBackofficeProjector:

```
@@ -31,7 +31,7 @@ public function applyBookingCreated(BookingCreated $event)
     {
         $this->repository->save(
             new BookingBackoffice(
-                $event->getId(),
+                $event->getCourtId(),
                 $event->getUserId(),
                 $event->getEmail(),
                 $event->getPhone(),
```
