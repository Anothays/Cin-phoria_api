<?php

namespace App\State;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Reservation;
use DateTime;
use DateTimeZone;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\MailerInterface;

class ReservationProcessor implements ProcessorInterface
{

    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($operation instanceof Post) {
            /** @var Reservation $data */
            $beginAt = $data->getProjectionEvent()->getBeginAt();
            $currentDate = new \DateTime("now", new DateTimeZone('Europe/Paris'));
            // dd($beginAt, $currentDate, $beginAt < $currentDate);
            if ($beginAt < $currentDate) throw new HttpException(500, "La séance n'est plus disponible");
            $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);
            return $result;
        }
        return $data;
    }
}
