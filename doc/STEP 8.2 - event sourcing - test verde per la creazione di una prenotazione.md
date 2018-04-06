STEP 8.2 - event soucing - test verde per la creazione di una prenotazione (CreateBookingTest)
===================================================

- Stato iniziale: lanciare `docker-compose exec php ./idephix.phar build` :

```
PHPUnit 6.5.7 by Sebastian Bergmann and contributors.

Testing Project Test Suite
SSSSSSSSSSSSF.........                                            22 / 22 (100%)

Time: 548 ms, Memory: 6.00MB

There was 1 failure:

1) App\Tests\Domain\Command\CreateBookingTest::should_create_valid_booking_if_court_is_available
Failed asserting that two arrays are equal.
--- Expected
+++ Actual
@@ @@
 Array (
-    0 => App\Domain\Event\BookingCreated Object (...)

/var/www/vendor/broadway/broadway/src/Broadway/CommandHandling/Testing/Scenario.php:107
/var/www/tests/Domain/Command/CreateBookingTest.php:62

FAILURES!
Tests: 22, Assertions: 15, Failures: 1, Skipped: 12.

```

- Verifica funzionamento dopo implementazione: `docker-compose exec php ./idephix.phar build`

````
PHPUnit 6.5.7 by Sebastian Bergmann and contributors.

Testing Project Test Suite
SSSSSSSSSSSS..........                                            22 / 22 (100%)

Time: 578 ms, Memory: 6.00MB

OK, but incomplete, skipped, or risky tests!
Tests: 22, Assertions: 15, Skipped: 12.
```


### FILE DA MODIFICARE:

- Modificare \App\Domain\BookingCommandHandler:

```
+use App\Domain\Aggregate\Court;
 use App\Domain\Command\AssignPromotion;
 use App\Domain\Command\CreateBooking;
 use App\Domain\Repository\UserRepository;
@@ -40,7 +41,12 @@ public function __construct(Repository $courtAggregateRepository, UserRepository
      */
     public function handleCreateBooking(CreateBooking $command)
     {
+        $user = $this->userRepository->find($command->getUserId());
 
+        $courtAggregate = new Court();
+        $courtAggregate->createBooking($command, $user);
+
+        $this->courtAggregateRepository->save($courtAggregate);
 //        $booking = Booking::fromCommand($command);
 //
 //        $booking->assertSlotLengthIsValid();
```

- Modificare \App\Domain\Aggregate\Court:

```
+use App\Domain\Command\CreateBooking;
+use App\Domain\Event\BookingCreated;
+use App\Domain\Model\User;
 use Broadway\EventSourcing\EventSourcedAggregateRoot;
 
 class Court extends EventSourcedAggregateRoot
 {
+    public function createBooking(CreateBooking $command, User $user)
+    {
+        $this->apply(
+            new BookingCreated(
+                $command->getCourtId(),
+                $command->getUserId(),
+                $user->getEmail(),
+                $user->getPhone(),
+                $command->getFrom(),
+                $command->getTo()
+            )
+        );
+    }
 
     /**
      * @return string
      */
     public function getAggregateRootId(): string
     {
-        // TODO: Implement getAggregateRootId() method.
+        return '';
     }
 }
```
