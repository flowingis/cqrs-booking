STEP 8.8 - event soucing - test verde per l'assegnazione della prenotazione gratuita e fix intera build
===================================================

- Stato iniziale: lanciare `docker-compose exec php ./idephix.phar build` : tutti i test verdi a parte 6 skipped 

- Verifica funzionamento dopo implementazione: `docker-compose exec php ./idephix.phar build`: tutti i test verdi

### FILE DA MODIFICARE:

- Modificare BookingControllerTest:

```
@@ -199,7 +199,6 @@ public function it_should_fail_when_booking_slot_time_end_after_23()
      */
     public function it_should_be_free_booking_when_booking_is_the_tenth()
     {
-        $this->markTestSkipped();
         $client = static::createClient();
```

- Modificare BookingCommandHandler sostituendo il seguente metodo:

```
     public function handleAssignPromotion(AssignPromotion $command)
     {
-//        $booking = $this->courtAvailabilityAggregateRepository->find($command->getId());
-//
-//        if (count($this->courtAvailabilityAggregateRepository->findAllByUser($command->getUserId())) === 10) {
-//            $booking->free();
-//            $this->courtAvailabilityAggregateRepository->update($booking);
-//        }
+        /** @var Court $courtAggregate */
+        $courtAggregate = $this->courtAggregateRepository->load($command->getCourtId());
+
+        $courtAggregate->assignPromotion($command->getUserId(), $command->getBookingUuid());
+
+        $this->courtAggregateRepository->save($courtAggregate);
     }
```

- Modifcare CreateBooking: 

```
 namespace App\Domain\Command;
 
 use Ramsey\Uuid\UuidInterface;;
+use Ramsey\Uuid\UuidInterface;
 
 class CreateBooking
 {
@@ -11,23 +12,36 @@ class CreateBooking
     private $to;
     private $free;
     private $id;
+    /**
+     * @var UuidInterface
+     */
+    private $bookingUuid;
 
     /**
      * CreateBooking constructor.
      *
-     * @param UuidInterface        $userId
-     * @param int                $id
+     * @param UuidInterface        $id
+     * @param int                $userId
      * @param \DateTimeImmutable $from
      * @param \DateTimeImmutable $to
      * @param string             $free
+     * @param UuidInterface      $bookingUuid
      */
-    public function __construct(UuidInterface $id, int $userId, \DateTimeImmutable $from, \DateTimeImmutable $to, string $free)
+    public function __construct(
+        UuidInterface $id,
+        int $userId,
+        \DateTimeImmutable $from,
+        \DateTimeImmutable $to,
+        string $free,
+        UuidInterface $bookingUuid
+    )
     {
         $this->id = $id;
         $this->userId = $userId;
         $this->from = $from;
         $this->to = $to;
         $this->free = $free;
+        $this->bookingUuid = $bookingUuid;
     }
 
     /**
@@ -69,4 +83,12 @@ public function getFree(): string
     {
         return $this->free;
     }
+
+    /**
+     * @return UuidInterface
+     */
+    public function getBookingUuid(): UuidInterface
+    {
+        return $this->bookingUuid;
+    }
 }
```

- Modifcare Court:

```
  use App\Domain\Command\CreateBooking;
 use App\Domain\Event\BookingCreated;
+use App\Domain\Event\PromotionAssigned;
 use App\Domain\Exception\SlotLengthInvalid;
 use App\Domain\Exception\SlotNotAvailable;
 use App\Domain\Exception\SlotTimeInvalid;
 use App\Domain\Model\Booking;
 use App\Domain\Model\User;
 use Ramsey\Uuid\UuidInterface;;
 use Broadway\EventSourcing\EventSourcedAggregateRoot;
+use Ramsey\Uuid\Uuid;
+use Ramsey\Uuid\UuidFactory;
+use Ramsey\Uuid\UuidInterface;
 
 class Court extends EventSourcedAggregateRoot
 {
@@ -41,7 +45,8 @@ public function createBooking(CreateBooking $command, User $user)
                 $user->getEmail(),
                 $user->getPhone(),
                 $command->getFrom(),
-                $command->getTo()
+                $command->getTo(),
+                $command->getBookingUuid()
             )
         );
     }
@@ -67,6 +72,26 @@ private function assertSlotIsAvailable(CreateBooking $createBooking)
         }
     }
 
+    /**
+     * @param int           $userId
+     * @param UuidInterface $bookingId
+     */
+    public function assignPromotion(int $userId, UuidInterface $bookingId)
+    {
+        $bookingPerUser = 0;
+        foreach ($this->bookings as $booking) {
+            if ($booking->getIdUser() === $userId) {
+                $bookingPerUser++;
+            }
+        }
+
+        if($bookingPerUser === 10){
+            $this->apply(
+                new PromotionAssigned($userId, $bookingId)
+            );
+        }
+    }
+
```

- Creare PromotionAssigned :

```
+<?php
+
+namespace App\Domain\Event;
+
+
+use Broadway\Serializer\Serializable;
+use Ramsey\Uuid\Uuid;
+use Ramsey\Uuid\UuidInterface;
+
+class PromotionAssigned implements Serializable
+{
+    /**
+     * @var int
+     */
+    private $userId;
+    /**
+     * @var UuidInterface
+     */
+    private $bookingUuid;
+
+    /**
+     * PromotionAssigned constructor.
+     *
+     * @param int           $userId
+     * @param UuidInterface $bookingUuid
+     */
+    public function __construct(int $userId, UuidInterface $bookingUuid)
+    {
+        $this->userId = $userId;
+        $this->bookingUuid = $bookingUuid;
+    }
+
+    /**
+     * @return int
+     */
+    public function getUserId(): int
+    {
+        return $this->userId;
+    }
+
+    /**
+     * @return UuidInterface
+     */
+    public function getBookingUuid(): UuidInterface
+    {
+        return $this->bookingUuid;
+    }
+
+    /**
+     * @return mixed The object instance
+     */
+    public static function deserialize(array $data)
+    {
+        return new self($data['userId'], Uuid::fromString($data['bookingUuid']));
+    }
+
+    /**
+     * @return array
+     */
+    public function serialize(): array
+    {
+        return [
+            'userId' => $this->userId,
+            'bookingUuid' => (string)$this->bookingUuid
+        ];
+    }
+}
```

- Modificare BookingProjector :

```
 namespace App\Domain\Projector;
 
 use App\Domain\Event\BookingCreated;
+use App\Domain\Event\PromotionAssigned;
 use App\Domain\ReadModel\Booking;
 use App\Domain\Repository\Repository;
 use Broadway\ReadModel\Projector;
 
 class BookingProjector extends Projector
 {
     /**
-     * @var Repository
+     * @var (Repository)
      */
     private $repository;
 
@@ -34,8 +35,22 @@ public function applyBookingCreated(BookingCreated $event)
                 $event->getCourtId(),
                 $event->getUserId(),
                 $event->getFrom(),
-                $event->getTo()
+                $event->getTo(),
+                $event->getBookingUuid()
             )
         );
     }
+
+    /**
+     * @param PromotionAssigned $event
+     */
+    public function applyPromotionAssigned(PromotionAssigned $event)
+    {
+        /** @var Booking $booking */
+        $booking = $this->repository->findByBookingId($event->getBookingUuid());
+
+        $booking->free();
+
+        $this->repository->update($booking);
+    }
```

- Modificare BookingRepository:

```
 use Ramsey\Uuid\UuidInterface;;
 use Broadway\ReadModel\Identifiable;
 use Doctrine\DBAL\Connection;
+use Ramsey\Uuid\Uuid;
+use Ramsey\Uuid\UuidInterface;
 
 /**
  * Class BookingRepository
@@ -40,18 +42,22 @@ public function save(Identifiable $booking): void
             "id_user" => $booking->getIdUser(),
             "date_from" => $booking->getFrom()->format('Y-m-d H:i'),
             "date_to" => $booking->getTo()->format('Y-m-d H:i'),
+            "booking_uuid" => (string)$booking->getBookingUuid()
         ]);
     }
 
     /**
-     * @param Model $booking
+     * @param Booking $booking
      */
-    public function update(Model $booking): void
+    public function update(Identifiable $booking): void
     {
         $this->connection->update(
             'booking',
             ["free" => $booking->isFree()],
-            ["uuid" => (string)$booking->getId()]
+            [
+                "uuid" => (string)$booking->getId(),
+                "booking_uuid" => (string)$booking->getBookingUuid()
+            ]
         );
     }
 
@@ -64,7 +70,7 @@ public function update(Model $booking): void
     public function find(UuidInterface $id) : ?Identifiable
     {
         $bookingData = $this->connection->fetchAssoc(
-            'select id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free from booking where uuid = :id',
+            'select id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free, booking_uuid from booking where uuid = :id',
             ["id" => $id]
         );
 
@@ -73,7 +79,9 @@ public function find(UuidInterface $id) : ?Identifiable
                 Uuid::fromString($bookingData['uuid']),
                 $bookingData['idUser'],
                 new \DateTimeImmutable($bookingData['from']),
-                new \DateTimeImmutable($bookingData['to'])
+                new \DateTimeImmutable($bookingData['to']),
+                Uuid::fromString($bookingData['booking_uuid']),
+                $bookingData['free']
             );
         }
 
@@ -108,16 +116,49 @@ public function findBookingByDay(\DateTimeImmutable $day) : array
     public function findAllByUser(int $userId) : array
     {
         $bookingsData = $this->connection->executeQuery(
-            'SELECT id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free FROM booking WHERE id_user=:id ORDER BY id ASC',
+            'SELECT id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free, booking_uuid FROM booking WHERE id_user=:id ORDER BY id ASC',
             ["id" => $userId]);
 
         $result = array();
 
         foreach ($bookingsData->fetchAll() as &$bookingData) {
-            $result[] = Booking::fromArray($bookingData);
+            $result[] = new Booking(
+                Uuid::fromString($bookingData['uuid']),
+                $bookingData['idUser'],
+                new \DateTimeImmutable($bookingData['from']),
+                new \DateTimeImmutable($bookingData['to']),
+                Uuid::fromString($bookingData['booking_uuid']),
+                $bookingData['free']
+            );
         }
 
         return $result;
     }
 
+    /**
+     * @param UuidInterface $bookingUuid
+     *
+     * @return Identifiable|null
+     * @throws \Assert\AssertionFailedException
+     */
+    public function findByBookingId(UuidInterface $bookingUuid) : ?Identifiable
+    {
+        $bookingData = $this->connection->fetchAssoc(
+            'select id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free, booking_uuid from booking where booking_uuid = :id',
+            ["id" => $bookingUuid]
+        );
+
+        if ($bookingData) {
+            return new Booking(
+                Uuid::fromString($bookingData['uuid']),
+                $bookingData['idUser'],
+                new \DateTimeImmutable($bookingData['from']),
+                new \DateTimeImmutable($bookingData['to']),
+                Uuid::fromString($bookingData['booking_uuid']),
+                $bookingData['free']
+            );
+        }
+
+        throw new ModelNotFound();
+    }
```

- Modificare Booking readmodel:

```
 use Ramsey\Uuid\UuidInterface;;
 use Broadway\ReadModel\Identifiable;
 use Broadway\ReadModel\SerializableReadModel;
+use Ramsey\Uuid\Uuid;
+use Ramsey\Uuid\UuidInterface;
 
 /**
  * Class Booking
@@ -33,6 +35,10 @@ class Booking implements Identifiable, SerializableReadModel
      * @var bool
      */
     private $free;
+    /**
+     * @var UuidInterface
+     */
+    private $bookingUuid;
 
     /**
      * Booking constructor.
@@ -41,17 +47,23 @@ class Booking implements Identifiable, SerializableReadModel
      * @param int                $idUser
      * @param \DateTimeImmutable $from
      * @param \DateTimeImmutable $to
+     * @param UuidInterface      $bookingUuid
+     * @param bool               $free
      */
     public function __construct(
         UuidInterface $id,
         int $idUser,
         \DateTimeImmutable $from,
-        \DateTimeImmutable $to
+        \DateTimeImmutable $to,
+        UuidInterface $bookingUuid,
+        bool $free = false
     ) {
         $this->idUser = $idUser;
         $this->from = $from;
         $this->to = $to;
         $this->id = $id;
+        $this->bookingUuid = $bookingUuid;
+        $this->free = $free;
     }
 
 
@@ -96,7 +108,8 @@ public static function deserialize(array $data)
             Uuid::fromString($data['id']),
             $data['idUser'],
             $data['from'],
-            $data['to']
+            $data['to'],
+            Uuid::fromString($data['bookingUuid'])
         );
     }
 
@@ -106,10 +119,32 @@ public static function deserialize(array $data)
     public function serialize(): array
     {
         return [
-            'id'     => (string)$this->id,
-            'idUser' => $this->idUser,
-            'from'   => $this->from,
-            'to'     => $this->to,
+            'id'          => (string)$this->id,
+            'idUser'      => $this->idUser,
+            'from'        => $this->from,
+            'to'          => $this->to,
+            'bookingUuid' => (string)$this->bookingUuid,
         ];
     }
+
+    /**
+     * @return bool
+     */
+    public function isFree(): bool
+    {
+        return $this->free;
+    }
+
+    /**
+     * @return UuidInterface
+     */
+    public function getBookingUuid(): UuidInterface
+    {
+        return $this->bookingUuid;
+    }
+
+    public function free()
+    {
+        $this->free = true;
+    }
 }
```

- Modificare PromotionAssignment:

```
     public function handleBookingCreated(BookingCreated $event)
     {
         $this->commandBus->dispatch(
-            new AssignPromotion($event->getCourtId(), $event->getUserId())
+            new AssignPromotion($event->getCourtId(), $event->getUserId(), $event->getBookingUuid())
         );
     }
 }
```

- Modificare AssignPromotion:

```
 namespace App\Domain\Command;
 
 use Ramsey\Uuid\UuidInterface;;
+use Ramsey\Uuid\UuidInterface;
 
 class AssignPromotion
 {
     private $userId;
     private $id;
+    /**
+     * @var UuidInterface
+     */
+    private $bookingUuid;
 
     /**
      * CreateBooking constructor.
      *
-     * @param UuidInterface $id
-     * @param int         $userId
+     * @param UuidInterface   $id
+     * @param int           $userId
+     * @param UuidInterface $bookingUuid
      */
-    public function __construct(UuidInterface $id, int $userId)
+    public function __construct(UuidInterface $id, int $userId, UuidInterface $bookingUuid)
     {
         $this->id = $id;
         $this->userId = $userId;
+        $this->bookingUuid = $bookingUuid;
     }
 
     /**
      * @return UuidInterface
      */
-    public function getId(): UuidInterface
+    public function getCourtId(): UuidInterface
     {
         return $this->id;
     }
@@ -36,4 +43,12 @@ public function getUserId(): int
     {
         return $this->userId;
     }
+
+    /**
+     * @return UuidInterface
+     */
+    public function getBookingUuid(): UuidInterface
+    {
+        return $this->bookingUuid;
+    }
 }
```

- Modificare BookingCreated:

```
 use Ramsey\Uuid\UuidInterface;;
 use Broadway\Serializer\Serializable;
+use Ramsey\Uuid\Uuid;
+use Ramsey\Uuid\UuidInterface;
 
 class BookingCreated implements Serializable
 {
@@ -32,6 +34,10 @@ class BookingCreated implements Serializable
      * @var \DateTimeImmutable
      */
     private $to;
+    /**
+     * @var Uuid
+     */
+    private $bookingUuid;
 
     /**
      * BookingCreated constructor.
@@ -42,21 +48,24 @@ class BookingCreated implements Serializable
      * @param string             $phone
      * @param \DateTimeImmutable $from
      * @param \DateTimeImmutable $to
+     * @param Uuid               $bookingUuid
      */
     public function __construct(
         UuidInterface $id,
         int $userId,
         string $email,
         string $phone,
         \DateTimeImmutable $from,
-        \DateTimeImmutable $to
+        \DateTimeImmutable $to,
+        UuidInterface $bookingUuid
     ) {
         $this->id = $id;
         $this->userId = $userId;
         $this->email = $email;
         $this->phone = $phone;
         $this->from = $from;
         $this->to = $to;
+        $this->bookingUuid = $bookingUuid;
     }
 
     /**
@@ -107,6 +116,14 @@ public function getTo(): \DateTimeImmutable
         return $this->to;
     }
 
+    /**
+     * @return Uuid
+     */
+    public function getBookingUuid(): Uuid
+    {
+        return $this->bookingUuid;
+    }
+
     /**
      * @return mixed The object instance
      */
@@ -118,7 +135,8 @@ public static function deserialize(array $data)
             $data['email'],
             $data['phone'],
             new \DateTimeImmutable($data['from']),
-            new \DateTimeImmutable($data['to'])
+            new \DateTimeImmutable($data['to']),
+            Uuid::fromString($data['bookingUuid'])
         );
     }
 
@@ -128,12 +146,13 @@ public static function deserialize(array $data)
     public function serialize(): array
     {
         return [
-            'id'     => (string)$this->id,
-            'userId' => $this->userId,
-            'email'  => $this->email,
-            'phone'  => $this->phone,
-            'from'   => $this->from->format('Y-m-d H:i'),
-            'to'     => $this->to->format('Y-m-d H:i'),
+            'id'          => (string)$this->id,
+            'userId'      => $this->userId,
+            'email'       => $this->email,
+            'phone'       => $this->phone,
+            'from'        => $this->from->format('Y-m-d H:i'),
+            'to'          => $this->to->format('Y-m-d H:i'),
+            'bookingUuid' => (string)$this->bookingUuid,
         ];
     }
 }
```

- Modificare CreateBookingTest:

```
     public function should_create_valid_booking_if_court_is_available()
     {
         $uuid = Uuid::uuid4();
+        $bookingUuid = Uuid::uuid4();
         $courtId = Uuid::fromString($uuid);
         $userId = 1;
         $from = new \DateTimeImmutable('2018-03-01 16:00');
@@ -40,7 +41,8 @@ public function should_create_valid_booking_if_court_is_available()
             $userId,
             $from,
             $to,
-            false
+            false,
+            $bookingUuid
         );
 
         $this->userRepository->find($userId)->willReturn(User::fromArray([
@@ -59,7 +61,8 @@ public function should_create_valid_booking_if_court_is_available()
                     $email,
                     $phone,
                     $createBooking->getFrom(),
-                    $createBooking->getTo()
+                    $createBooking->getTo(),
+                    $bookingUuid
                 )
             ]);
     }
```

- Modificare BookingBackofficeProjectorTest:

```
@@ -27,7 +27,8 @@ public function should_create_booking_read_model()
             'user@email.it',
             '0349043904',
             new \DateTimeImmutable('2018-03-01 10:00'),
-            new \DateTimeImmutable('2018-03-01 11:00')
+            new \DateTimeImmutable('2018-03-01 11:00'),
+            Uuid::uuid4()
         );
         $bookingBackofficeReadModel = new BookingBackoffice(
             Uuid::fromString($uuid), 
```

- ModelNotFound BookingController:

```
@@ -35,7 +35,8 @@ public function create(Request $request, LoggerInterface $logger)
                     $bookingData['idUser'],
                     new \DateTimeImmutable($bookingData['from']),
                     new \DateTimeImmutable($bookingData['to']),
-                    $bookingData['free']
+                    $bookingData['free'],
+                    Uuid::uuid4()
                 )
             );
             return new JsonResponse(["courtId" => (string)$courtId], 201);
``

