<?php

// src/EventListener/SearchIndexer.php
namespace App\EventListener;

use App\Entity\ProjectionEvent;
use App\Repository\ProjectionEventRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsDoctrineListener(event: Events::prePersist, priority: 500, connection: 'default')]
class OnCreateProjectionEventListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
      $em = $args->getObjectManager();
      
      if ($args->getObject() instanceof ProjectionEvent) {
        /** @var ProjectionEvent $newProjectionEvent */
        $newProjectionEvent = $args->getObject();
        $newBeginAt = $newProjectionEvent->getBeginAt();
        $newEndAt = $newProjectionEvent->getEndAt();
        $projectionRoom = $newProjectionEvent->getProjectionRoom();
        /** @var ProjectionEventRepository $projectionEventRepo */
        $projectionEventRepo = $em->getRepository(ProjectionEvent::class);
        /** @var ProjectionEvent[] $existingProjectionEvents */
        $existingProjectionEvents = $projectionEventRepo
        ->createQueryBuilder('pe')
        ->where('pe.projectionRoom = :room')
        ->andWhere('pe.beginAt BETWEEN :newBeginAt AND :newEndAt')
        ->setParameter('room', $projectionRoom)
        ->setParameter('newBeginAt', $newBeginAt)
        ->setParameter('newEndAt', $newEndAt)
        ->getQuery()
        ->getResult();
        if (count($existingProjectionEvents) > 0) throw new BadRequestHttpException('Salle déjà occupée pendant cet intervalle.');
        // checl for endAt computed property
        foreach ($existingProjectionEvents as $existingProjectionEvent) { 
          if ($existingProjectionEvent->getEndAt() > $newBeginAt && $existingProjectionEvent->getEndAt() < $newEndAt) {
            throw new BadRequestHttpException('Le temps de séance entre en conflit avec une autre séance.');
          }
        }
        
        return;
      }
        

    }


}