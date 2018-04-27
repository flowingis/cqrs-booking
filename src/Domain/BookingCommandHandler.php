<?php

namespace App\Domain;


use App\Domain\Aggregate\Court;
use App\Domain\Command\AssignPromotion;
use App\Domain\Command\CreateBooking;
use App\Domain\Repository\UserRepository;
use Broadway\CommandHandling\SimpleCommandHandler;
use Broadway\Repository\Repository;

class BookingCommandHandler extends SimpleCommandHandler
{
    /**
     * @var Repository
     */
    private $courtAggregateRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * BookingCommandHandler constructor.
     *
     * @param Repository     $courtAggregateRepository
     * @param UserRepository $userRepository
     */
    public function __construct(Repository $courtAggregateRepository, UserRepository $userRepository)
    {
        $this->courtAggregateRepository = $courtAggregateRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param CreateBooking $command
     *
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function handleCreateBooking(CreateBooking $command)
    {
        $user = $this->userRepository->find($command->getUserId());

        /** @var Court $courtAggregate */
        try {
            $courtAggregate = $this->courtAggregateRepository->load($command->getCourtId());
            $courtAggregate->createBooking($command, $user);
        } catch (\Broadway\Repository\AggregateNotFoundException $exception) {
            $courtAggregate = new Court();
            $courtAggregate->createBooking($command, $user);
        }

        $this->courtAggregateRepository->save($courtAggregate);
    }

    public function handleAssignPromotion(AssignPromotion $command)
    {
        /** @var Court $courtAggregate */
        $courtAggregate = $this->courtAggregateRepository->load($command->getCourtId());

        $courtAggregate->assignPromotion($command->getUserId(), $command->getBookingUuid());

        $this->courtAggregateRepository->save($courtAggregate);
    }
}
