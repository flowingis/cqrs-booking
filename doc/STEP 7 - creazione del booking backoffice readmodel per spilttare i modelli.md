STEP 7 - creazione del booking backoffice readmodel per spilttare i modelli
===================================================

- Stato iniziale: lanciare `docker-compose exec php ./idephix.phar build` :

```
Local: bin/phpunit -c ./ --group functional
#!/usr/bin/env php
PHPUnit 6.5.7 by Sebastian Bergmann and contributors.

Testing Project Test Suite
E......                                                             7 / 7 (100%)

Time: 6.32 seconds, Memory: 20.00MB

There was 1 error:

1) App\Tests\Controller\BookingControllerTest::it_should_create_booking_and_create_booking_read_model
Undefined offset: 0

/var/www/tests/Controller/BookingControllerTest.php:51

ERRORS!
Tests: 7, Assertions: 15, Errors: 1.
```

- Verifica funzionamento dopo implementazione: `docker-compose exec php ./idephix.phar build` tutti i test verdi 
 
### FILE DA MODIFICARE:

- Creare BookingBackofficeProjector:

```
+<?php
+
+namespace App\Domain\Projector;
+
+use App\Domain\Event\BookingCreated;
+use App\Domain\ReadModel\BookingBackoffice;
+use App\Domain\Repository\Repository;
+use Broadway\ReadModel\Projector;
+
+class BookingBackofficeProjector extends Projector
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
+            new BookingBackoffice(
+                $event->getId(),
+                $event->getUserId(),
+                $event->getEmail(),
+                $event->getPhone(),
+                $event->getFrom(),
+                $event->getTo()
+            )
+        );
+    }
+}
```

- Creare BookingBackofficeRepository:

```
+<?php
+
+namespace App\Domain\Repository;
+
+
+use App\Domain\Exception\ModelNotFound;
+use App\Domain\Model\Booking;
+use App\Domain\Model\Model;
+use App\Domain\ReadModel\BookingBackoffice;
+use Ramsey\Uuid\UuidInterface;;
+use Doctrine\DBAL\Connection;
+
+/**
+ * Class BookingRepository
+ * @package App\Domain\Repository
+ */
+class BookingBackofficeRepository implements Repository
+{
+    /**
+     * @var Connection
+     */
+    private $connection;
+
+    /**
+     * BookingRepository constructor.
+     * @param Connection $connection
+     */
+    public function __construct(Connection $connection)
+    {
+        $this->connection = $connection;
+    }
+
+    /**
+     * @param Model $bookingBackoffice
+     */
+    public function save(Model $bookingBackoffice): void
+    {
+        $this->connection->insert('booking_backoffice', [
+            "uuid" => (string)$bookingBackoffice->getId(),
+            "id_user" => $bookingBackoffice->getUserId(),
+            "date_from" => $bookingBackoffice->getFrom()->format('Y-m-d H:i'),
+            "date_to" => $bookingBackoffice->getTo()->format('Y-m-d H:i'),
+            "email" => $bookingBackoffice->getEmail(),
+            "phone" => $bookingBackoffice->getPhone()
+        ]);
+    }
+
+    public function find(UuidInterface $id): ?Model
+    {
+        // TODO: Implement find() method.
+    }
+}
```

- Creare read model BookingBackoffice:

```
+<?php
+
+namespace App\Domain\ReadModel;
+
+
+use App\Domain\Model\Model;
+use Ramsey\Uuid\UuidInterface;;
+
+class BookingBackoffice implements Model
+{
+    /**
+     * @var UuidInterface
+     */
+    private $id;
+    /**
+     * @var int
+     */
+    private $userId;
+    /**
+     * @var string
+     */
+    private $email;
+    /**
+     * @var string
+     */
+    private $phone;
+    /**
+     * @var \DateTimeImmutable
+     */
+    private $from;
+    /**
+     * @var \DateTimeImmutable
+     */
+    private $to;
+
+    public function __construct(
+        UuidInterface $id,
+        int $userId,
+        string $email,
+        string $phone,
+        \DateTimeImmutable $from,
+        \DateTimeImmutable $to
+    ) {
+
+        $this->id = $id;
+        $this->userId = $userId;
+        $this->email = $email;
+        $this->phone = $phone;
+        $this->from = $from;
+        $this->to = $to;
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
+
+    /**
+     * @return string
+     */
+    public function getEmail(): string
+    {
+        return $this->email;
+    }
+
+    /**
+     * @return string
+     */
+    public function getPhone(): string
+    {
+        return $this->phone;
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
+}
```

- Modificare BookingCreated aggiungendo email, phone, from, to:

```
@@ -15,17 +15,47 @@ class BookingCreated
      * @var int
      */
     private $userId;
+    /**
+     * @var string
+     */
+    private $email;
+    /**
+     * @var string
+     */
+    private $phone;
+    /**
+     * @var \DateTimeImmutable
+     */
+    private $from;
+    /**
+     * @var \DateTimeImmutable
+     */
+    private $to;
 
     /**
      * BookingCreated constructor.
      *
-     * @param UuidInterface $id
-     * @param int         $userId
+     * @param UuidInterface        $id
+     * @param int                $userId
+     * @param string             $email
+     * @param string             $phone
+     * @param \DateTimeImmutable $from
+     * @param \DateTimeImmutable $to
      */
-    public function __construct(UuidInterface $id, int $userId)
-    {
+    public function __construct(
+        UuidInterface $id,
+        int $userId,
+        string $email,
+        string $phone,
+        \DateTimeImmutable $from,
+        \DateTimeImmutable $to
+    ) {
         $this->id = $id;
         $this->userId = $userId;
+        $this->email = $email;
+        $this->phone = $phone;
+        $this->from = $from;
+        $this->to = $to;
     }
 
     /**
@@ -43,4 +73,36 @@ public function getUserId(): int
     {
         return $this->userId;
     }
+
+    /**
+     * @return string
+     */
+    public function getEmail(): string
+    {
+        return $this->email;
+    }
+
+    /**
+     * @return string
+     */
+    public function getPhone(): string
+    {
+        return $this->phone;
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
 }
```

- Modificare BookingCommanHandler aggiungere la pubblicazione dell'evento per scatenare la creazione del nuovo readmodel: 

```
 @@ -7,8 +7,10 @@
 use App\Domain\Command\CreateBooking;
 use App\Domain\Event\BookingCreated;
 use App\Domain\Model\Booking;
+use App\Domain\Model\User;
 use App\Domain\Repository\BookingRepository;
 use App\Domain\Repository\Repository;
+use App\Domain\Repository\UserRepository;
 use Broadway\CommandHandling\SimpleCommandHandler;
 use Broadway\Domain\DomainEventStream;
 use Broadway\Domain\DomainMessage;
@@ -25,17 +27,23 @@ class BookingCommandHandler extends SimpleCommandHandler
      * @var EventBus
      */
     private $eventBus;
+    /**
+     * @var UserRepository
+     */
+    private $userRepository;
 
     /**
      * BookingCommandHandler constructor.
      *
-     * @param Repository $repository
-     * @param EventBus   $eventBus
+     * @param Repository     $repository
+     * @param UserRepository $userRepository
+     * @param EventBus       $eventBus
      */
-    public function __construct(Repository $repository, EventBus $eventBus)
+    public function __construct(Repository $repository, UserRepository $userRepository, EventBus $eventBus)
     {
         $this->bookingRepository = $repository;
         $this->eventBus = $eventBus;
+        $this->userRepository = $userRepository;
     }
 
     /**
@@ -58,14 +66,23 @@ public function handleCreateBooking(CreateBooking $command)
 
         $this->bookingRepository->save($booking);
 
+        $user = $this->userRepository->find($command->getUserId());
+
         $this->eventBus->publish(
             new DomainEventStream(
                 [
                     DomainMessage::recordNow(
                         $command->getId(),
                         0,
                         new Metadata([]),
-                        new BookingCreated($command->getId(), $command->getUserId())
+                        new BookingCreated(
+                            $command->getId(),
+                            $command->getUserId(),
+                            $user->getEmail(),
+                            $user->getPhone(),
+                            $command->getFrom(),
+                            $command->getTo()
+                        )
                     )
                 ]
             )
```

- Modificare services.yaml:

```
@@ -30,6 +30,7 @@ services:
         class: App\Domain\BookingCommandHandler
         arguments:
           - '@App\Domain\Repository\BookingRepository'
+          - '@App\Domain\Repository\UserRepository'
           - '@broadway.event_handling.event_bus'
         tags:
             - { name: broadway.command_handler }
@@ -52,6 +53,12 @@ services:
             tags:
                 - { name: broadway.domain.event_listener }
 
+    App\Domain\Projector\BookingBackofficeProjector:
+        class: App\Domain\Projector\BookingBackofficeProjector
+        arguments: ['@App\Domain\Repository\BookingBackofficeRepository']
+        tags:
+            - { name: broadway.domain.event_listener }
+
```

- Modificare BookingCommandHandlerTest:

```
@@ -6,7 +6,9 @@
 use App\Domain\Command\AssignPromotion;
 use App\Domain\Command\CreateBooking;
 use App\Domain\Model\Booking;
+use App\Domain\Model\User;
 use App\Domain\Repository\BookingRepository;
+use App\Domain\Repository\UserRepository;
 use Ramsey\Uuid\UuidInterface;;
 use PHPUnit\Framework\TestCase;
 use Ramsey\Uuid\Uuid;
@@ -19,22 +21,31 @@ class BookingCommandHandlerTest extends TestCase
     public function should_create_booking()
     {
         $bookingRepository = $this->prophesize(BookingRepository::class);
+        $userRepository = $this->prophesize(UserRepository::class);
         $eventBus = $this->prophesize(\Broadway\EventHandling\EventBus::class);
         $from = new \DateTimeImmutable('2018-03-01 15:00');
         $uuid = Uuid::uuid4();
+        $userId = 1;
         $expectedBooking = Booking::fromArray([
             'uuid' => $uuid,
-            'idUser' => 1,
+            'idUser' => $userId,
             'from' => '2018-03-01 15:00',
             'to' => '2018-03-01 16:00',
             'free' => false
         ]);
+        $user = User::fromArray([
+            'id' => $userId,
+            'email' => 'banana@example.it',
+            'phone' => '329430490'
+        ]);
 
         $commandHandler = new BookingCommandHandler(
             $bookingRepository->reveal(),
+            $userRepository->reveal(),
             $eventBus->reveal()
         );
 
+        $userRepository->find($userId)->willReturn($user);
         $bookingRepository->findBookingByDay($from)->willReturn([]);
         $bookingRepository->save($expectedBooking)->shouldBeCalled();
 
@@ -60,6 +71,7 @@ public function should_not_create_booking_for_invalid_slot_length()
 
         $commandHandler = new BookingCommandHandler(
             $bookingRepository->reveal(),
+            $this->prophesize(UserRepository::class)->reveal(),
             $eventBus->reveal()
         );
 
@@ -85,6 +97,7 @@ public function should_not_create_booking_when_court_are_closed()
 
         $commandHandler = new BookingCommandHandler(
             $bookingRepository->reveal(),
+            $this->prophesize(UserRepository::class)->reveal(),
             $eventBus->reveal()
         );
 
@@ -119,6 +132,7 @@ public function should_not_create_booking_for_not_available_slots()
 
         $commandHandler = new BookingCommandHandler(
             $bookingRepository->reveal(),
+            $this->prophesize(UserRepository::class)->reveal(),
             $eventBus->reveal()
         );
 
@@ -162,6 +176,7 @@ public function should_assign_promotion()
 
         $commandHandler = new BookingCommandHandler(
             $bookingRepository->reveal(),
+            $this->prophesize(UserRepository::class)->reveal(),
             $eventBus->reveal()
         );
```
