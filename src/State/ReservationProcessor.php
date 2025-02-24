<?php

namespace App\State;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use App\Constant\ErrorMessages;
use App\Entity\Reservation;
use DateTimeZone;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReservationProcessor implements ProcessorInterface
{

    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        /** @var Reservation $data */
        $projectionEvent = $data->getProjectionEvent();
        $currentDate = new \DateTime("now", new DateTimeZone('Europe/Paris'));
        
        if ($operation instanceof Post) {
            if ($projectionEvent->getBeginAt() < $currentDate) {
                $this->removeProcessor->process($data, $operation, $uriVariables, $context);
                throw new NotFoundHttpException(ErrorMessages::PROJECTION_EVENT_NOT_AVAILABLE);
            } 
            
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
            
        } elseif ($operation instanceof Patch) {
            if ($data->getCreatedAt() < $currentDate->modify('-5 minutes')) {
                $this->removeProcessor->process($data, $operation, $uriVariables, $context);
                throw new NotFoundHttpException(ErrorMessages::RESERVATION_TIMEOUT);
            }
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }
        return $data;
    }
}


