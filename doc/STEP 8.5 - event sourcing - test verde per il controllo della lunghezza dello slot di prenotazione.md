STEP 8.5 - event soucing - test verde per il controllo della lunghezza dello slot di prenotazione
===================================================

- Stato iniziale: lanciare `docker-compose exec php ./idephix.phar build` : tutti i test verdi a parte 10 skipped 

- Verifica funzionamento dopo implementazione: `docker-compose exec php ./idephix.phar build`: tutti i test verdi a parte 8 skipped

### FILE DA MODIFICARE:

- Aggiungere a \App\Tests\Controller\BookingControllerTest:

```
@@ -95,7 +95,6 @@ public function it_should_fail_when_booking_slots_are_overlapping()
      */
     public function it_should_fail_when_booking_slot_are_shorter_than_1h()
     {
-        $this->markTestSkipped();
         $client = static::createClient();
         $container = $client->getContainer();
         $container->get('doctrine.dbal.default_connection')->query('truncate booking');
@@ -122,7 +121,6 @@ public function it_should_fail_when_booking_slot_are_shorter_than_1h()
      */
     public function it_should_fail_when_booking_slot_are_longer_than_3h()
     {
-        $this->markTestSkipped();
         $client = static::createClient();
         $container = $client->getContainer();
         $container->get('doctrine.dbal.default_connection')->query('truncate booking');

```


- Aggiungere a \App\Domain\Aggregate\Court:

```
 use App\Domain\Command\CreateBooking;
 use App\Domain\Event\BookingCreated;
+use App\Domain\Exception\SlotLengthInvalid;
 use App\Domain\Exception\SlotNotAvailable;
 use App\Domain\Model\Booking;
 use App\Domain\Model\User;
@@ -12,6 +13,9 @@
 
 class Court extends EventSourcedAggregateRoot
 {
+    const ONE_HOUR_TIMESTAMP = 1 * 60 * 60;
+    const THREE_HOURS_TIMESTAMP = 3 * 60 * 60;
+
     /**
      * @var Booking[]
      */
@@ -23,6 +27,7 @@ class Court extends EventSourcedAggregateRoot
 
     public function createBooking(CreateBooking $command, User $user)
     {
+        $this->assertSlotLengthIsValid($command);
         $this->assertSlotIsAvailable($command);
 
         $this->apply(
@@ -73,6 +78,26 @@ protected function applyBookingCreated(BookingCreated $event)
         );
     }
 
+    /**
+     * @param CreateBooking $command
+     *
+     * @return bool
+     */
+    private function assertSlotLengthIsValid(CreateBooking $command): bool
+    {
+        $diff = $command->getTo()->getTimestamp() - ($command->getFrom()->getTimestamp());
+
+        if ($diff < self::ONE_HOUR_TIMESTAMP) {
+            throw new SlotLengthInvalid();
+        }
+
+        if ($diff > self::THREE_HOURS_TIMESTAMP) {
+            throw new SlotLengthInvalid();
+        }
+
+        return true;
+    }
+
```
