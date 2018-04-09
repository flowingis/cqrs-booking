<?php

namespace App\Domain\Process;


use App\Domain\Command\AssignPromotion;
use App\Domain\Event\BookingCreated;
use Broadway\CommandHandling\CommandBus;
use Broadway\Processor\Processor;

class PromotionAssignment extends Processor
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function handleBookingCreated(BookingCreated $event)
    {
        $this->commandBus->dispatch(
            new AssignPromotion($event->getId(), $event->getUserId())
        );
    }
}
