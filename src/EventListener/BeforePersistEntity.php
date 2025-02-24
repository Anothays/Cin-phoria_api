<?php

// src/EventListener/SearchIndexer.php
namespace App\EventListener;

use App\Document\Ticket as TicketDoc;
use App\Entity\ProjectionEvent;
use App\Entity\Ticket;
use App\Repository\ProjectionEventRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsDoctrineListener(event: Events::prePersist, priority: 500, connection: 'default')]
class BeforePersistEntity
{
    public function __construct(private DocumentManager $dm) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
      $em = $args->getObjectManager();
      if ($args->getObject() instanceof ProjectionEvent) {
        /** @var ProjectionEvent $newProjectionEvent */
        $newProjectionEvent = $args->getObject();
        $newBeginAt = $newProjectionEvent->getBeginAt();
        $newEndAt = $newProjectionEvent->getEndAt();
        $date = new DateTimeImmutable($newBeginAt->format('Ymd'));
        // dd($date);
        $projectionRoom = $newProjectionEvent->getProjectionRoom();
        /** @var ProjectionEventRepository $projectionEventRepo */
        $projectionEventRepo = $em->getRepository(ProjectionEvent::class);
        /** @var ProjectionEvent[] $existingProjectionEvents */
        $existingProjectionEvents = $projectionEventRepo
        ->createQueryBuilder('pe')
        ->where('pe.projectionRoom = :room')
        ->andWhere('pe.beginAt >= :startOfDay')
        ->andWhere('pe.beginAt <= :endOfDay')
        ->setParameter('room', $projectionRoom)
        ->setParameter('startOfDay', $date)
        ->setParameter('endOfDay', $date->modify("+1days"))
        ->getQuery()
        ->getResult();
        // checl for endAt computed property
        foreach ($existingProjectionEvents as $existingProjectionEvent) { 
          // dd($existingProjectionEvent->getBeginAt(), $existingProjectionEvent->getEndAt(), $newBeginAt, $newEndAt);
          if (($existingProjectionEvent->getBeginAt() > $newBeginAt && $existingProjectionEvent->getBeginAt() < $newEndAt) 
            ||
            ($existingProjectionEvent->getEndAt() > $newBeginAt && $existingProjectionEvent->getEndAt() < $newEndAt)) {
            throw new BadRequestHttpException("L'horaire de la séance entre en conflit avec une autre séance.");
          }
        }
        return;
      } elseif (($ticket = $args->getObject()) instanceof Ticket) { // GENERATE TICKET INTO NOSQL DATABASE
        /** @var Ticket $ticket */
        $ticketDoc = new TicketDoc(
          $ticket->getProjectionEvent()->getMovie()->getTitle(), 
          $ticket->getCategory()->getCategoryName(), 
          $ticket->getCategory()->getPrice()
        );
        $this->dm->persist($ticketDoc);
        $this->dm->flush();
        return;
      }
    }
}




