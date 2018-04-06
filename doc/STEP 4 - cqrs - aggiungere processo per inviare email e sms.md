STEP 4 - cqrs - aggiungere processo per inviare email e sms
===========================================

- Stato iniziale: lanciare `docker-compose exec php ./idephix.phar build` : tutti test verdi e uno skipped

- Verifica funzionamento dopo implementazione: `docker-compose exec php ./bin/phpunit -c ./ --filter="it_should_create_booking"` 
 e controllare che in `tests/fixtures/notifications` ci siano i due file txt che rappresentano le notifiche

### FILE DA MODIFICARE:

- Modificare Mailer:

```
     public function send(string $to, string $message): void
     {
+        file_put_contents(__DIR__.'/../../tests/fixtures/notifications/mail.txt', $to . ' ### ' . $message);
     }
```

- Modificare Sms:

```
public function send(string $phone, string $message): void
     {
+        file_put_contents(__DIR__.'/../../tests/fixtures/notifications/sms.txt', $phone . ' ### ' . $message);
     }
```

- Creare tests/fixtures/notifications/mail.txt e tests/fixtures/notifications/sms.txt vuoti

- Creare il processor MailNotification:

```
+<?php
+
+namespace App\Domain\Process;
+
+
+use App\Domain\Command\AssignPromotion;
+use App\Domain\Event\BookingCreated;
+use App\Domain\Repository\UserRepository;
+use App\Service\Mailer;
+use Broadway\Processor\Processor;
+
+class MailNotification extends Processor
+{
+    /**
+     * @var Mailer
+     */
+    private $mailer;
+    /**
+     * @var UserRepository
+     */
+    private $userRepository;
+
+    public function __construct(Mailer $mailer, UserRepository $userRepository)
+    {
+        $this->mailer = $mailer;
+        $this->userRepository = $userRepository;
+    }
+
+    public function handleBookingCreated(BookingCreated $event)
+    {
+        $user = $this->userRepository->find($event->getUserId());
+        $this->mailer->send($user->getEmail(), 'Booked!');
+    }
+}
```

- Creare il file SmsNotification:

```
+<?php
+
+namespace App\Domain\Process;
+
+
+use App\Domain\Event\BookingCreated;
+use App\Domain\Repository\UserRepository;
+use App\Service\Sms;
+use Broadway\Processor\Processor;
+
+class SmsNotification extends Processor
+{
+    /**
+     * @var Sms
+     */
+    private $sms;
+    /**
+     * @var UserRepository
+     */
+    private $userRepository;
+
+    public function __construct(Sms $sms, UserRepository $userRepository)
+    {
+        $this->sms = $sms;
+        $this->userRepository = $userRepository;
+    }
+
+    public function handleBookingCreated(BookingCreated $event)
+    {
+        $user = $this->userRepository->find($event->getUserId());
+        $this->sms->send($user->getPhone(), 'Booked!');
+    }
+}
```

- Modificare services.yaml:

```
+    App\Domain\Process\MailNotification:
+            class: App\Domain\Process\MailNotification
+            arguments: ['@App\Service\Mailer', '@App\Domain\Repository\UserRepository']
+            tags:
+                - { name: broadway.domain.event_listener }
+
+    App\Domain\Process\SmsNotification:
+            class: App\Domain\Process\SmsNotification
+            arguments: ['@App\Service\Sms', '@App\Domain\Repository\UserRepository']
+            tags:
+                - { name: broadway.domain.event_listener }
```
