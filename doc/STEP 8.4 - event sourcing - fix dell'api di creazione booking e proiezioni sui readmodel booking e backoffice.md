STEP 8.4 - event soucing - fix dell'api di creazione booking e proiezioni sui readmodel booking e backoffice
===================================================

- Stato iniziale: lanciare `docker-compose exec php ./idephix.phar build` : tutti i test verdi a parte 12 skipped 


- Verifica funzionamento dopo implementazione: `docker-compose exec php ./idephix.phar build`: tutti i test verdi a parte 10 skipped

### FILE DA MODIFICARE:


- Aggiungere a \App\Tests\Controller\BookingControllerTest:

```
/**
 * @test
 */
public function it_should_create_booking_and_create_booking_read_model()
{
    ...
    $container->get('doctrine.dbal.default_connection')->query('truncate booking_backoffice');
    $container->get('doctrine.dbal.default_connection')->query('truncate events');
    ...
    $booking = $container->get('App\Domain\Repository\BookingRepository')->find(
            Uuid::fromString(json_decode($client->getResponse()->getContent(), true)["courtId"])
        );
    ...
}

/**
 * @test
 */
public function it_should_fail_when_booking_slots_are_overlapping()
{
    ...
    $container->get('doctrine.dbal.default_connection')->query('truncate booking_backoffice');
    $container->get('doctrine.dbal.default_connection')->query('truncate events');
	...
}
```

- Modificare \App\Controller\BookingController per la generazione dell'id del campo che per l'esercizio sarà statico dato che abbiamo un solo campo da tennis:

```
@@ -26,19 +26,19 @@ public function create(Request $request, LoggerInterface $logger)
     {
         try {
             $bookingData = json_decode($request->getContent(), true);
-            $bookingId = Uuid::uuid4();
+            $courtId = Uuid::fromString('ac4198ff-5f17-4c97-85ee-8e6175720e47');
             $commandBus = $this->get('broadway.command_handling.command_bus');
 
             $commandBus->dispatch(
                 new CreateBooking(
-                    $bookingId,
+                    $courtId,
                     $bookingData['idUser'],
                     new \DateTimeImmutable($bookingData['from']),
                     new \DateTimeImmutable($bookingData['to']),
                     $bookingData['free']
                 )
             );
-            return new JsonResponse(["bookingId" => (string)$bookingId], 201);
+            return new JsonResponse(["courtId" => (string)$courtId], 201);
         } catch (ModelNotFound $e) {
             return new JsonResponse(["error" => $e->getMessage()], 404);
         } catch (\DomainException $e) {
```

- Aggiungere a \App\Domain\Aggregate\Court:

```
 use App\Domain\Exception\SlotNotAvailable;
 use App\Domain\Model\Booking;
 use App\Domain\Model\User;
+use Ramsey\Uuid\UuidInterface;;
 use Broadway\EventSourcing\EventSourcedAggregateRoot;
 
 class Court extends EventSourcedAggregateRoot
@@ -15,6 +16,10 @@ class Court extends EventSourcedAggregateRoot
      * @var Booking[]
      */
     private $bookings = [];
+    /**
+     * @var UuidInterface
+     */
+    private $id;
 
     public function createBooking(CreateBooking $command, User $user)
     {
@@ -55,6 +60,8 @@ private function assertSlotIsAvailable(CreateBooking $createBooking)
 
     protected function applyBookingCreated(BookingCreated $event)
     {
+        $this->id = $event->getCourtId();
+
         $this->bookings[] = Booking::fromArray(
             [
                 'uuid' => $event->getCourtId(),
@@ -71,6 +78,6 @@ protected function applyBookingCreated(BookingCreated $event)
      */
     public function getAggregateRootId(): string
     {
-        return '';
+        return (string)$this->id;
     }
 }
```

- Aggiungere a \App\Domain\Event\BookingCreated i metodi deserialize e serialize per poter salvare gli eventi nel db:

```
 use Ramsey\Uuid\UuidInterface;;
+use Broadway\Serializer\Serializable;
 
-class BookingCreated
+class BookingCreated implements Serializable
 {
     /**
      * @var UuidInterface
@@ -105,4 +106,34 @@ public function getTo(): \DateTimeImmutable
     {
         return $this->to;
     }
+
+    /**
+     * @return mixed The object instance
+     */
+    public static function deserialize(array $data)
+    {
+        return new self(
+            Uuid::fromString($data['id']),
+            $data['userId'],
+            $data['email'],
+            $data['phone'],
+            new \DateTimeImmutable($data['from']),
+            new \DateTimeImmutable($data['to'])
+        );
+    }
+
+    /**
+     * @return array
+     */
+    public function serialize(): array
+    {
+        return [
+            'id'     => (string)$this->id,
+            'userId' => $this->userId,
+            'email'  => $this->email,
+            'phone'  => $this->phone,
+            'from'   => $this->from->format('Y-m-d H:i'),
+            'to'     => $this->to->format('Y-m-d H:i'),
+        ];
+    }
 }
```

- Da qua in poi, facciamo i fix per far funzionare i readmodel. 
Modificare \App\Domain\Process\PromotionAssignment:

```
@@ -23,7 +23,7 @@ public function __construct(CommandBus $commandBus)
     public function handleBookingCreated(BookingCreated $event)
     {
         $this->commandBus->dispatch(
-            new AssignPromotion($event->getId(), $event->getUserId())
+            new AssignPromotion($event->getCourtId(), $event->getUserId())
         );
     }
 }
```

- Modificare \App\Domain\Projector\BookingBackofficeProjector:

```
/**
     * @param BookingCreated $event
     */
    public function applyBookingCreated(BookingCreated $event)
    {
        $this->repository->save(
            new BookingBackoffice(
                $event->getCourtId(),
                $event->getUserId(),
                $event->getEmail(),
                $event->getPhone(),
                $event->getFrom(),
                $event->getTo()
            )
        );
    }
```

- Creare \App\Domain\Projector\BookingProjector:

```
+<?php
+
+namespace App\Domain\Projector;
+
+use App\Domain\Event\BookingCreated;
+use App\Domain\ReadModel\Booking;
+use App\Domain\Repository\Repository;
+use Broadway\ReadModel\Projector;
+
+class BookingProjector extends Projector
+{
+    /**
+     * @var Repository
+     */
+    private $repository;
+
+    /**
+     * BookingBackofficeProjector constructor.
+     *
+     * @param Repository $repository
+     */
+    public function __construct(Repository $repository)
+    {
+        $this->repository = $repository;
+    }
+
+    /**
+     * @param BookingCreated $event
+     */
+    public function applyBookingCreated(BookingCreated $event)
+    {
+        $this->repository->save(
+            new Booking(
+                $event->getCourtId(),
+                $event->getUserId(),
+                $event->getFrom(),
+                $event->getTo()
+            )
+        );
+    }
+}
```

- Aggiungere \App\Domain\ReadModel\Booking:

```
+<?php
+
+namespace App\Domain\ReadModel;
+
+
+use Ramsey\Uuid\UuidInterface;;
+use Broadway\ReadModel\Identifiable;
+use Broadway\ReadModel\SerializableReadModel;
+
+/**
+ * Class Booking
+ * @package App\Domain\Model
+ */
+class Booking implements Identifiable, SerializableReadModel
+{
+    /**
+     * @var int
+     */
+    private $idUser;
+    /**
+     * @var \DateTimeImmutable
+     */
+    private $from;
+    /**
+     * @var \DateTimeImmutable
+     */
+    private $to;
+    /**
+     * @var UuidInterface;
+     */
+    private $id;
+    /**
+     * @var bool
+     */
+    private $free;
+
+    /**
+     * Booking constructor.
+     *
+     * @param UuidInterface        $id
+     * @param int                $idUser
+     * @param \DateTimeImmutable $from
+     * @param \DateTimeImmutable $to
+     */
+    public function __construct(
+        UuidInterface $id,
+        int $idUser,
+        \DateTimeImmutable $from,
+        \DateTimeImmutable $to
+    ) {
+        $this->idUser = $idUser;
+        $this->from = $from;
+        $this->to = $to;
+        $this->id = $id;
+    }
+
+
+    /**
+     * @return int
+     */
+    public function getIdUser(): int
+    {
+        return $this->idUser;
+    }
+
+    /**
+     * @return \DateTimeImmutable
+     */
+    public function getFrom(): \DateTimeImmutable
+    {
+        return $this->from;
+    }
+
+    /**
+     * @return \DateTimeImmutable
+     */
+    public function getTo(): \DateTimeImmutable
+    {
+        return $this->to;
+    }
+
+    /**
+     * @return string
+     */
+    public function getId(): string
+    {
+        return (string)$this->id;
+    }
+
+    /**
+     * @return mixed The object instance
+     */
+    public static function deserialize(array $data)
+    {
+        return new self(
+            Uuid::fromString($data['id']),
+            $data['idUser'],
+            $data['from'],
+            $data['to']
+        );
+    }
+
+    /**
+     * @return array
+     */
+    public function serialize(): array
+    {
+        return [
+            'id'     => (string)$this->id,
+            'idUser' => $this->idUser,
+            'from'   => $this->from,
+            'to'     => $this->to,
+        ];
+    }
+}
```

- Aggiungere su services.yaml:

```
+    App\Domain\Projector\BookingProjector:
+        class: App\Domain\Projector\BookingProjector
+        arguments: ['@App\Domain\Repository\BookingRepository']
+        tags:
+            - { name: broadway.domain.event_listener }
+
```

- Modificare \App\Domain\ReadModel\BookingBackoffice:

```
 use App\Domain\Model\Model;
 use Ramsey\Uuid\UuidInterface;;
+use Broadway\ReadModel\Identifiable;
 
-class BookingBackoffice implements Model
+class BookingBackoffice implements Identifiable
 {
     /**
      * @var UuidInterface
@@ -53,9 +54,9 @@ public function __construct(
     /**
      * @return UuidInterface
      */
-    public function getId(): UuidInterface
+    public function getId(): string
     {
-        return $this->id;
+        return (string)$this->id;
     }
```

- Modificare \App\Domain\Repository\BookingBackofficeRepository:

```
 use App\Domain\Model\Model;
 use App\Domain\ReadModel\BookingBackoffice;
 use Ramsey\Uuid\UuidInterface;;
+use Broadway\ReadModel\Identifiable;
 use Doctrine\DBAL\Connection;
 
 /**
@@ -33,7 +34,7 @@ public function __construct(Connection $connection)
     /**
      * @param Model $bookingBackoffice
      */
-    public function save(Model $bookingBackoffice): void
+    public function save(Identifiable $bookingBackoffice): void
     {
         $this->connection->insert('booking_backoffice', [
             "uuid" => (string)$bookingBackoffice->getId(),
@@ -45,7 +46,7 @@ public function save(Model $bookingBackoffice): void
         ]);
     }
 
-    public function find(UuidInterface $id): ?Model
+    public function find(UuidInterface $id): ?Identifiable
     {
         // TODO: Implement find() method.
     }
```

- Modificare \App\Domain\Repository\BookingRepository:

```
 <?php
-/**
- * Created by PhpStorm.
- * User: saverio
- * Date: 03/04/18
- * Time: 11.59
- */
 
 namespace App\Domain\Repository;
 
 
 use App\Domain\Exception\ModelNotFound;
-use App\Domain\Model\Booking;
 use App\Domain\Model\Model;
+use App\Domain\ReadModel\Booking;
 use Ramsey\Uuid\UuidInterface;;
+use Broadway\ReadModel\Identifiable;
 use Doctrine\DBAL\Connection;
-use SebastianBergmann\Comparator\Book;
 
 /**
  * Class BookingRepository
@@ -37,9 +31,9 @@ public function __construct(Connection $connection)
     }
 
     /**
-     * @param Model $booking
+     * @param Identifiable $booking
      */
-    public function save(Model $booking): void
+    public function save(Identifiable $booking): void
     {
         $this->connection->insert('booking', [
             "uuid" => (string)$booking->getId(),
@@ -63,18 +57,24 @@ public function update(Model $booking): void
 
     /**
      * @param UuidInterface $id
-     * @return Booking|null
-     * @throws \Exception
+     *
+     * @return Identifiable|null
+     * @throws \Assert\AssertionFailedException
      */
-    public function find(UuidInterface $id) : ?Model
+    public function find(UuidInterface $id) : ?Identifiable
     {
         $bookingData = $this->connection->fetchAssoc(
             'select id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free from booking where uuid = :id',
             ["id" => $id]
         );
 
         if ($bookingData) {
-            return Booking::fromArray($bookingData);
+            return new Booking(
+                Uuid::fromString($bookingData['uuid']),
+                $bookingData['idUser'],
+                new \DateTimeImmutable($bookingData['from']),
+                new \DateTimeImmutable($bookingData['to'])
+            );
         }
 
         throw new ModelNotFound();
```

- Modificare \App\Domain\Repository\Repository:

```
<?php
-/**
- * Created by PhpStorm.
- * User: saverio
- * Date: 06/04/18
- * Time: 17.38
- */
 
 namespace App\Domain\Repository;
 
-
-use App\Domain\Model\Model;
 use Ramsey\Uuid\UuidInterface;;
+use Broadway\ReadModel\Identifiable;
 
 interface Repository
 {
-    public function save(Model $model) : void;
-    public function find(UuidInterface $id) : ?Model;
+    public function save(Identifiable $model) : void;
+    public function find(UuidInterface $id) : ?Identifiable;
 }
```
