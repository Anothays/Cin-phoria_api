<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TicketRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ApiResource()]
#[UniqueEntity('uniqueCode')]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 36)]
    #[Groups(['reservation'])]
    private ?string $uniqueCode = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    private ?Reservation $reservation = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TicketCategory $category = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->uniqueCode = Uuid::v4();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUniqueCode(): ?string
    {
        return $this->uniqueCode;
    }

    // public function setUniqueCode(string $uniqueCode): static
    // {
    //     $this->uniqueCode = $uniqueCode;

    //     return $this;
    // }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): static
    {
        $this->reservation = $reservation;

        return $this;
    }


    public function getCategory(): ?TicketCategory
    {
        return $this->category;
    }

    public function setCategory(?TicketCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**+
     *  CUSTOM FUNCTIONS
     */
    public function getMovie(): ?Movie
    {
        return $this->reservation->getProjectionEvent()->getMovie();
    }

    public function getProjectionEvent(): ?ProjectionEvent
    {
        return $this->reservation->getProjectionEvent();
    }

    public function getProjectionFormat(): ?ProjectionFormat
    {
        return $this->getProjectionEvent()->getFormat();
    }

    public function getProjectionEventDateStart(): ?\DateTime
    {
        return $this->reservation->getProjectionEvent()->getBeginAt();
    }

    public function getProjectionRoom(): ?ProjectionRoom
    {
        return $this->getProjectionEvent()->getProjectionRoom();
    }

    public function getMovieTheater(): ?MovieTheater
    {
        return $this->getProjectionRoom()->getMovieTheater();
    }

    public function getPrice(): ?int
    {
        $price = $this->getCategory()->getPrice();
        $extraCharge = $this->getReservation()->getProjectionEvent()->getFormat()->getExtraCharge();
        return  $price + $extraCharge;
    }


  

}
