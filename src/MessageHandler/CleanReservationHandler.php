<?php

namespace App\MessageHandler;

use App\Entity\Reservation;
use App\Message\CleanReservationMessage;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use function Symfony\Component\Clock\now;

#[AsMessageHandler]
final class CleanReservationHandler
{

    public function __construct(private ReservationRepository $reservationRepository, private EntityManagerInterface $em) {}

    public function __invoke(CleanReservationMessage $message): void
    {
        $limitDate = new \DateTime();
        $limitDate->modify('-5 minutes');
        $this->em->createQueryBuilder()
            ->delete(Reservation::class, 'r')
            ->where('r.isPaid = false')
            ->andWhere('r.createdAt < :limitDate')
            ->setParameter('limitDate', $limitDate)
            ->getQuery()
            ->execute();

    }
}

