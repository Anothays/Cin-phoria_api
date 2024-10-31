<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document]
class Ticket
{
    #[MongoDB\Id]
    public readonly ?string $id;

    #[MongoDB\Field(type: 'date_immutable')]
    public readonly \DateTimeImmutable $createdAt;

    #[MongoDB\Field(type: 'string')]
    public readonly string $movieTitle;
    
    #[MongoDB\Field(type: 'string')]
    public readonly string $ticketCategory;
    
    #[MongoDB\Field(type: 'int')]
    public readonly int $price;

    public function __construct(string $movieTitle, string $ticketCategory, int $price)
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->movieTitle = $movieTitle;
        $this->ticketCategory = $ticketCategory;
        $this->price = $price;
    }
}