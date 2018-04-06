STEP 8.3 - event soucing - test rosso per la creazione di una prenotazione con slot occupato (CreateBookingTest::should_not_create_booking_for_not_available_slots)
===================================================

- Verifica funzionamento dopo implementazione: `docker-compose exec php ./idephix.phar build`:

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

### FILE DA MODIFICARE:

Aggiungere a \App\Tests\Domain\Command\CreateBookingTest:

```
+    /**
+     * @test
+     * @expectedException \App\Domain\Exception\SlotNotAvailable
+     */
+    public function should_not_create_booking_for_not_available_slots()
+    {
+        $uuid = Uuid::uuid4();
+        $courtId = Uuid::fromString($uuid);
+        $userId1 = 1;
+        $userId2 = 2;
+        $from = new \DateTimeImmutable('2018-03-01 16:00');
+        $to = new \DateTimeImmutable('2018-03-01 17:00');
+        $email = 'banana@example.com';
+        $phone = '3296734555';
+
+        $bookingCreated = new BookingCreated(
+            $courtId,
+            $userId1,
+            $email,
+            $phone,
+            $from,
+            $to
+        );
+
+        $createBooking = new CreateBooking(
+            $courtId,
+            $userId2,
+            $from,
+            new \DateTimeImmutable('2018-03-01 18:00'),
+            false
+        );
+
+        $this->userRepository->find($userId2)->willReturn(User::fromArray([
+            'id' => $userId2,
+            'email' => 'anans@example.com',
+            'phone' => '3245678987'
+        ]));
+
+        $this->scenario
+            ->given([$bookingCreated])
+            ->when($createBooking);
+    }
+

```
