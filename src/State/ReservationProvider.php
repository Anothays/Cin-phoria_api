<?php

namespace App\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Reservation;
use App\Entity\User;
use App\Security\Jwt;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReservationProvider implements ProviderInterface
{

    public function __construct(private EntityManagerInterface $em, private Jwt $jwtHandler) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {

        // Créer une instance de JWT.php et refactoriser le code
        $token = $this->jwtHandler->getJwtFromHttpHeaders();
        if (!$token) throw new Exception('Not authorized');
        $payload = $this->jwtHandler->decodeJwt($token);
        $user = $this->em->getRepository(User::class)->findOneBy([
            'email' => $payload['username'],
        ]);

        if ($operation instanceof GetCollection) {
            $reservations = $this->em->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->join('r.projectionEvent', 'p')
            ->where('r.user = :user')
            ->andWhere('r.isPaid = true')
            ->setParameter('user', $user)
            ->orderBy('p.beginAt', 'DESC')
            ->getQuery()
            ->getResult();
            return $reservations;

        } elseif ($operation instanceof Get) {
            $reservations = $this->em->getRepository(Reservation::class)->findOneBy([
                'id' => $uriVariables["id"],
                'user' => $user,
            ]);
            if (!$reservations) throw new NotFoundHttpException('Pas de réservation trouvée');
            return $reservations;
        }
        

    }
}
