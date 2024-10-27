<?php

namespace App\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;

class CommentStateProvider implements ProviderInterface
{

    public function __construct(private EntityManagerInterface $em) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {

        if ($operation instanceof GetCollection) {
            $queries = $context['filters'] ?? [];
            $criterias = ['isVerified' => true];
            foreach ($queries as $key => $query) {
                if (property_exists(Comment::class, $key))
                $criterias[$key] = $query; 
            }

            $commentRepo = $this->em->getRepository(Comment::class);
            return $commentRepo->findBy($criterias);
        }
    
    }
}
