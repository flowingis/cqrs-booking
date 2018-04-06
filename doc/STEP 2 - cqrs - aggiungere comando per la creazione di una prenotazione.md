STEP 2 - cqrs - aggiungere comando per la creazione di una prenotazione
===========================================

- Stato iniziale: lanciare `docker-compose exec php ./idephix.phar build` : 

```
Testing Project Test Suite
E.....S                                                             7 / 7 (100%)

Time: 3.73 seconds, Memory: 18.00MB

There was 1 error:

1) App\Tests\Controller\BookingControllerTest::it_should_create_booking
Assert\InvalidArgumentException: Value "1" is not a valid UUID.

/var/www/vendor/beberlei/assert/lib/Assert/Assertion.php:288
/var/www/vendor/beberlei/assert/lib/Assert/Assertion.php:1835
/var/www/src/Domain/ValueObject/UuidInterface.php:23
/var/www/tests/Controller/BookingControllerTest.php:39

ERRORS!
Tests: 7, Assertions: 11, Errors: 1, Skipped: 1.
```

- Verifica funzionamento dopo implementazione: `docker-compose exec php ./idephix.phar build` deve essere verde (con il solito file skipped)


### FILE DA MODIFICARE:

- Modificare BookingController:

```
<?php
 namespace App\Controller;
 
+use App\Domain\Command\CreateBooking;
 use App\Domain\Exception\ModelNotFound;
-use App\Domain\Exception\SlotLengthInvalid;
-use App\Domain\Exception\SlotNotAvailable;
-use App\Domain\Exception\SlotTimeInvalid;
 use App\Domain\Service\BookingCreator;
+use Ramsey\Uuid\UuidInterface;;
+use Ramsey\Uuid\Uuid;
+use Psr\Log\LoggerInterface;
 use Symfony\Component\HttpFoundation\JsonResponse;
 use Symfony\Component\HttpFoundation\Request;
 
@@ -16,18 +17,30 @@ class BookingController
      * @param BookingCreator $bookingCreator
      * @return JsonResponse
      */
-    public function create(Request $request, BookingCreator $bookingCreator)
+    public function create(Request $request, BookingCreator $bookingCreator, LoggerInterface $logger)
     {
         try {
-            $booking = $bookingCreator->create(json_decode($request->getContent(), true));
-            return new JsonResponse(["bookingId" => $booking->getId()], 201);
+            $bookingData = json_decode($request->getContent(), true);
+            $bookingId = Uuid::fromString(Uuid::uuid4());
+            $booking = $bookingCreator->create(
+                new CreateBooking(
+                    $bookingId,
+                    $bookingData['idUser'],
+                    new \DateTimeImmutable($bookingData['from']),
+                    new \DateTimeImmutable($bookingData['to']),
+                    $bookingData['free']
+                )
+            );
+            return new JsonResponse(["bookingId" => (string)$bookingId], 201);
         } catch (ModelNotFound $e) {
             return new JsonResponse(["error" => $e->getMessage()], 404);
         } catch (\DomainException $e) {
             return new JsonResponse(["message" => $e->getMessage()], 400);
-        } catch (\Exception $e) {
+        }
+        catch (\Exception $e) {
+            $logger->critical($e->getMessage() . ' #### ' . $e->getTraceAsString());
             return new JsonResponse(["error" => $e->getMessage(), "stack" => $e->getTraceAsString()], 500);
         }
     }
 
-} 
+}
```

- Creare il comando CreateBooking:

```
+<?php
+
+namespace App\Domain\Command;
+
+use Ramsey\Uuid\UuidInterface;;
+
+class CreateBooking
+{
+    private $userId;
+    private $from;
+    private $to;
+    private $free;
+    private $id;
+
+    public function __construct(UuidInterface $id, int $userId, \DateTimeImmutable $from, \DateTimeImmutable $to, string $free)
+    {
+        $this->id = $id;
+        $this->userId = $userId;
+        $this->from = $from;
+        $this->to = $to;
+        $this->free = $free;
+    }
+
+    public function getId(): UuidInterface
+    {
+        return $this->id;
+    }
+
+    public function getUserId(): int
+    {
+        return $this->userId;
+    }
+
+    public function getFrom(): \DateTimeImmutable
+    {
+        return $this->from;
+    }
+
+    public function getTo(): \DateTimeImmutable
+    {
+        return $this->to;
+    }
+
+    public function getFree(): string
+    {
+        return $this->free;
+    }
+}
```

- Modificare BookingCreator commentando il codice:

```
 namespace App\Domain\Service;
 
+use App\Domain\Command\CreateBooking;
 use App\Domain\Model\Booking;
 use App\Domain\Repository\BookingRepository;
 use App\Domain\Repository\UserRepository;
 use App\Service\Mailer;
 use App\Service\Sms;
+use Broadway\CommandHandling\CommandBus;
 
 /**
  * Class BookingCreator
  * @package App\Domain\Service
  */
 class BookingCreator
 {
-    /**
-     * @var BookingRepository
-     */
-    private $bookingRepository;
+    private $commandBus;
     /**
      * @var Mailer
      */
@@ -30,60 +29,58 @@ class BookingCreator
      * @var UserRepository
      */
     private $userRepository;
+    /**
+     * @var BookingRepository
+     */
+    private $bookingRepository;
 
     /**
      * BookingCreator constructor.
+     *
      * @param BookingRepository $bookingRepository
-     * @param UserRepository $userRepository
-     * @param Mailer $mailer
-     * @param Sms $sms
+     * @param CommandBus        $commandBus
+     * @param UserRepository    $userRepository
+     * @param Mailer            $mailer
+     * @param Sms               $sms
      */
     public function __construct(
         BookingRepository $bookingRepository,
+        CommandBus $commandBus,
         UserRepository $userRepository,
         Mailer $mailer,
         Sms $sms
     ) {
-        $this->bookingRepository = $bookingRepository;
+        $this->commandBus = $commandBus;
         $this->mailer = $mailer;
         $this->sms = $sms;
         $this->userRepository = $userRepository;
+        $this->bookingRepository = $bookingRepository;
     }
 
     /**
-     * @param array $bookingData
+     * @param CreateBooking $bookingData
      * @return Booking
      * @throws \Exception
      */
-    public function create(array $bookingData) : Booking
+    public function create(CreateBooking $createBooking)
     {
         // booking creation
-        $booking = Booking::fromArray($bookingData);
-
-        $booking->assertSlotLengthIsValid();
-        $booking->assertTimeIsValid();
-
-        $bookingOfDay = $this->bookingRepository->findBookingByDay($booking->getFrom());
-        foreach ($bookingOfDay as &$b) {
-            $booking->assertSlotIsAvailable($b);
-        }
-
-        $bookingId = $this->bookingRepository->save($booking);
+        $this->commandBus->dispatch($createBooking);
         // end booking creation
 
         //booking promotion
-        $booking = $this->bookingRepository->find($bookingId);
-        if (count($this->bookingRepository->findAllByUser($booking->getIdUser())) === 10) {
-            $booking->free();
-            $this->bookingRepository->save($booking);
-        }
+//        $booking = $this->bookingRepository->find($bookingId);
+//        if (count($this->bookingRepository->findAllByUser($createBooking->getUserId())) === 10) {
+//            $booking->free();
+//            $this->bookingRepository->save($booking);
+//        }
         // end booking promotion
 
         // booking notification
-        $user = $this->userRepository->find($booking->getIdUser());
-
-        $this->mailer->send($user->getEmail(), 'Booked!');
-        $this->sms->send($user->getPhone(), 'Booked!');
+//        $user = $this->userRepository->find($booking->getIdUser());
+//
+//        $this->mailer->send($user->getEmail(), 'Booked!');
+//        $this->sms->send($user->getPhone(), 'Booked!');
         // booking notification
 
         return $booking;
```

- Creare il command handler BookingCreatorCommandHandler con il codice di creazione della prenotazione copiato dal servizio BookingCreator:

```
+<?php
+
+namespace App\Domain;
+
+
+use App\Domain\Command\CreateBooking;
+use App\Domain\Model\Booking;
+use App\Domain\Repository\BookingRepository;
+use App\Domain\Repository\Repository;
+use Broadway\CommandHandling\SimpleCommandHandler;
+
+class BookingCommandHandler extends SimpleCommandHandler
+{
+    /**
+     * @var BookingRepository
+     */
+    private $bookingRepository;
+
+    /**
+     * BookingCommandHandler constructor.
+     *
+     * @param Repository $repository
+     */
+    public function __construct(Repository $repository)
+    {
+        $this->bookingRepository = $repository;
+    }
+
+    /**
+     * @param CreateBooking $command
+     *
+     * @return int
+     * @throws \Doctrine\DBAL\DBALException
+     */
+    public function handleCreateBooking(CreateBooking $command)
+    {
+        $booking = Booking::fromCommand($command);
+
+        $booking->assertSlotLengthIsValid();
+        $booking->assertTimeIsValid();
+
+        $bookingOfDay = $this->bookingRepository->findBookingByDay($booking->getFrom());
+        foreach ($bookingOfDay as &$b) {
+            $booking->assertSlotIsAvailable($b);
+        }
+
+        $this->bookingRepository->save($booking);
+    }
+}
```

- Decommentare nel services.yaml:

```
+    App\Domain\Service\BookingCreator:
+        class: App\Domain\Service\BookingCreator
+        arguments:
+          - '@App\Domain\Repository\BookingRepository'
+          - '@broadway.command_handling.command_bus'
+          - '@App\Domain\Repository\UserRepository'
+          - '@App\Service\Mailer'
+          - '@App\Service\Sms'
+
+    App\Domain\BookingCommandHandler:
+        class: App\Domain\BookingCommandHandler
+        arguments: ['@App\Domain\Repository\BookingRepository']
+        tags:
+            - { name: broadway.command_handler }
```

- Modificare il modello Booking:

```
namespace App\Domain\Model;
 

+use App\Domain\Command\CreateBooking;
 use App\Domain\Exception\SlotLengthInvalid;
 use App\Domain\Exception\SlotNotAvailable;
 use App\Domain\Exception\SlotTimeInvalid;
+use Ramsey\Uuid\UuidInterface;;
 
 /**
  * Class Booking
@@ -41,9 +43,8 @@ class Booking implements Model
      * @var \DateTimeImmutable
      */
     private $to;
-
     /**
-     * @var int;
+     * @var UuidInterface;
      */
     private $id;
     /**
@@ -57,15 +58,14 @@ class Booking implements Model
      * @param \DateTimeImmutable $from
      * @param \DateTimeImmutable $to
      * @param bool $free
-     * @param int|null $id
      */
     private function __construct(
+        UuidInterface $id,
         int $idUser,
         \DateTimeImmutable $from,
         \DateTimeImmutable $to,
-        bool $free,
-        int $id = null)
-    {
+        bool $free
+    ) {
         $this->idUser = $idUser;
         $this->from = $from;
         $this->to = $to;
@@ -80,20 +80,30 @@ private function __construct(
      */
     public static function fromArray(array $bookingData) : Booking
     {
-
         return new self(
+            Uuid::fromString($bookingData['uuid']),
             $bookingData['idUser'],
             new \DateTimeImmutable($bookingData['from']),
             new \DateTimeImmutable($bookingData['to']),
-            $bookingData['free'],
-            $bookingData['id'] ?? null
+            $bookingData['free']
+        );
+    }
+
+    public static function fromCommand(CreateBooking $createBooking) : Booking
+    {
+        return new self(
+            $createBooking->getId(),
+            $createBooking->getUserId(),
+            $createBooking->getFrom(),
+            $createBooking->getTo(),
+            $createBooking->getFree()
         );
     }
 
     /**
-     * @return int
+     * @return UuidInterface
      */
-    public function getId(): ?int
+    public function getId(): UuidInterface
     {
         return $this->id;
     }
```

- Modificare BookingRepository:

```
use App\Domain\Exception\ModelNotFound;
 use App\Domain\Model\Booking;
 use App\Domain\Model\Model;
+use Ramsey\Uuid\UuidInterface;;
 use Doctrine\DBAL\Connection;
 use SebastianBergmann\Comparator\Book;
 
@@ -36,40 +37,27 @@ public function __construct(Connection $connection)
     }
 
     /**
-     * @param Booking $booking
-     * @return int
+     * @param Model $booking
      */
-    public function save(Model $booking) : int
+    public function save(Model $booking): void
     {
-        if ($booking->getId()) {
-            $this->connection->update('booking', [
-                "date_from" => $booking->getFrom()->format('Y-m-d H:i'),
-                "date_to" => $booking->getTo()->format('Y-m-d H:i'),
-                "free" => $booking->isFree()
-            ],
-            ["id" => $booking->getId()]);
-
-            return $booking->getId();
-        }
-
         $this->connection->insert('booking', [
+            "uuid" => (string)$booking->getId(),
             "id_user" => $booking->getIdUser(),
             "date_from" => $booking->getFrom()->format('Y-m-d H:i'),
             "date_to" => $booking->getTo()->format('Y-m-d H:i'),
         ]);
-
-        return $this->connection->lastInsertId();
     }
 
     /**
-     * @param int $id
+     * @param UuidInterface $id
      * @return Booking|null
      * @throws \Exception
      */
-    public function find(int $id) : ?Model
+    public function find(UuidInterface $id) : ?Model
     {
         $bookingData = $this->connection->fetchAssoc(
-            'select id, id_user as idUser, date_from as `from`, date_to as `to`, free from booking where id = :id',
+            'select id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free from booking where uuid = :id',
             ["id" => $id]
         );
 
@@ -88,7 +76,7 @@ public function find(int $id) : ?Model
     public function findBookingByDay(\DateTimeImmutable $day) : array
     {
         $bookingsData = $this->connection->executeQuery(
-            'SELECT id, id_user as idUser, date_from as `from`, date_to as `to`, free FROM booking WHERE DATE(date_from)=:date',
+            'SELECT id, uuid, id_user as idUser, date_from as `from`, date_to as `to`, free FROM booking WHERE DATE(date_from)=:date',
             ["date" => $day->format('Y-m-d')]);

```

- Modificare l'interfaccia Repository:

```
use App\Domain\Model\Model;
+use Ramsey\Uuid\UuidInterface;;
 
 interface Repository
 {
-    public function save(Model $model) : int;
-    public function find(int $id) : ?Model;
-} 
+    public function save(Model $model) : void;
+    public function find(UuidInterface $id) : ?Model;
+}
```

- Modificare UserRepository:

```
  * Class UserRepository
  * @package App\Domain\Repository
  */
-class UserRepository implements Repository
+class UserRepository
 {
     /**
      * @var Connection
@@ -34,15 +34,6 @@ public function __construct(Connection $connection)
         $this->connection = $connection;
     }
 
-    /**
-     * @param Model $user
-     * @return int
-     */
-    public function save(Model $user): int
-    {
-        // TODO: Implement save() method.
-    }
-
```
