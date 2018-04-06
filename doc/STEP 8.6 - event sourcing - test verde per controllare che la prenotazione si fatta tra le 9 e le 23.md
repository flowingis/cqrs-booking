STEP 8.6 - event soucing - test verde per controllare che la prenotazione si fatta tra le 9 e le 23
===================================================

- Stato iniziale: lanciare `docker-compose exec php ./idephix.phar build` : tutti i test verdi a parte 8 skipped 

- Verifica funzionamento dopo implementazione: `docker-compose exec php ./idephix.phar build`: tutti i test verdi a parte 6 skipped

### FILE DA MODIFICARE:


- Aggiungere a \App\Tests\Controller\BookingControllerTest:

```
@@ -147,7 +147,6 @@ public function it_should_fail_when_booking_slot_are_longer_than_3h()
      */
     public function it_should_fail_when_booking_slot_time_start_before_9()
     {
-        $this->markTestSkipped();
         $client = static::createClient();
         $container = $client->getContainer();
         $container->get('doctrine.dbal.default_connection')->query('truncate booking');
@@ -174,7 +173,6 @@ public function it_should_fail_when_booking_slot_time_start_before_9()
      */
     public function it_should_fail_when_booking_slot_time_end_after_23()
     {
-        $this->markTestSkipped();
```


- Aggiungere a \App\Domain\Aggregate\Court:

```
 use App\Domain\Event\BookingCreated;
 use App\Domain\Exception\SlotLengthInvalid;
 use App\Domain\Exception\SlotNotAvailable;
+use App\Domain\Exception\SlotTimeInvalid;
 use App\Domain\Model\Booking;
 use App\Domain\Model\User;
 use Ramsey\Uuid\UuidInterface;;
@@ -15,6 +16,8 @@ class Court extends EventSourcedAggregateRoot
 {
     const ONE_HOUR_TIMESTAMP = 1 * 60 * 60;
     const THREE_HOURS_TIMESTAMP = 3 * 60 * 60;
+    const FIRST_HOUR_BOOKABLE = 9;
+    const LAST_HOUR_BOOKABLE = 23;
 
     /**
      * @var Booking[]
@@ -28,6 +31,7 @@ class Court extends EventSourcedAggregateRoot
     public function createBooking(CreateBooking $command, User $user)
     {
         $this->assertSlotLengthIsValid($command);
+        $this->assertTimeIsValid($command);
         $this->assertSlotIsAvailable($command);
 
         $this->apply(
@@ -98,6 +102,42 @@ private function assertSlotLengthIsValid(CreateBooking $command): bool
         return true;
     }
 
+    /**
+     * @return bool
+     */
+    private function assertTimeIsValid(CreateBooking $command): bool
+    {
+        $fromHour = intval($command->getFrom()->format('H'), 10);
+        $fromMinute = intval($command->getFrom()->format('i'), 10);
+        $toHour = intval($command->getTo()->format('H'), 10);
+        $toMinute = intval($command->getTo()->format('i'), 10);
+
+        if (self::isHourValid($fromHour, $fromMinute) and self::isHourValid($toHour, $toMinute)) {
+            return true;
+        }
+
+        throw new SlotTimeInvalid();
+    }
+
+    /**
+     * @param int $hour
+     * @param int $minute
+     * @return bool
+     */
+    private static function isHourValid(int $hour, int $minute): bool
+    {
+        if ($hour < self::FIRST_HOUR_BOOKABLE) {
+            return false;
+        }
+
+        if ($hour > self::LAST_HOUR_BOOKABLE or ($hour == self::LAST_HOUR_BOOKABLE and $minute > 0)) {
+            return false;
+        }
+
+        return true;
+    }
+
+
```
