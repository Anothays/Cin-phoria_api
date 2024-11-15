<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Put;
use App\Repository\ReservationRepository;
use App\State\ReservationProcessor;
use App\State\ReservationProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['reservation']],
    denormalizationContext: ['groups' => ['reservation:write']],
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_USER")', 
            provider: ReservationProvider::class
        ),
        new Get(
            security: 'is_granted("ROLE_USER")',
            provider: ReservationProvider::class
        ),
        new Post(
            security: 'is_granted("ROLE_USER")',
            processor: ReservationProcessor::class,
        ),
        new Put(
            security: 'is_granted("ROLE_USER")',
        ),
        new Patch(
            security: 'is_granted("ROLE_USER")'
        )
    ],
)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reservation', 'reservation:write'])]
    private ?int $id = null;

    #[ORM\Column( options: ['default' => false])]
    #[Groups(['reservation'])]
    private ?bool $isPaid = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[Groups(['reservation', 'reservation:write'])]
    private ?User $user = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'reservation')]
    #[MaxDepth(1)]
    #[Groups(['reservation'])]
    private Collection $tickets;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['reservation', 'reservation:write'])]
    private ?ProjectionEvent $projectionEvent = null;

    /**
     * @var Collection<int, ProjectionRoomSeat>
     */
    #[ORM\ManyToMany(targetEntity: ProjectionRoomSeat::class, inversedBy: 'reservations')]
    #[Groups(['reservation', 'reservation:write'])]
    private Collection $seats;

    #[ORM\Column]
    #[Groups(['reservation', 'reservation:write'])]
    private ?bool $hasRate = false;

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
        $this->tickets = new ArrayCollection();
        $this->seats = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setPaid(bool $isPaid): static
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    #[ApiProperty(description: "Le prix total calculé à partir de chaque billet et du type de séance")]
    #[Groups(['reservation', 'reservation:write'])]
    public function getTotalPrice(): ?int
    {
        $reducer = function($carry, Ticket $item) {
            return $carry + $item->getCategory()->getPrice();
        };
        $amount = array_reduce([...$this->tickets], $reducer , 0);
        $extraCharge = $this->projectionEvent->getFormat()->getExtraCharge();
        return $amount + $extraCharge;
    }

    // PRIVATE
    // private function setTotalPrice(int $totalPrice): static
    // {
    //     $this->totalPrice = $totalPrice;

    //     return $this;
    // }

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): static
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setReservation($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getReservation() === $this) {
                $ticket->setReservation(null);
            }
        }

        return $this;
    }

    public function getProjectionEvent(): ?ProjectionEvent
    {
        return $this->projectionEvent;
    }

    public function setProjectionEvent(?ProjectionEvent $projectionEvent): static
    {
        $this->projectionEvent = $projectionEvent;

        return $this;
    }

    /**
     * @return Collection<int, ProjectionRoomSeat>
     */
    public function getSeats(): Collection
    {
        return $this->seats;
    }

    public function addSeat(ProjectionRoomSeat $seat): static
    {
        // Prevent from adding seat from another projection_room
        // if ($seat->getProjectionRoom() !== $this->getProjectionEvent()->getProjectionRoom()) return $this; 

        if (!$this->seats->contains($seat)) {
            $this->seats->add($seat);
        }

        return $this;
    }

    public function removeSeat(ProjectionRoomSeat $seat): static
    {
        $this->seats->removeElement($seat);

        return $this;
    }

    public function getMovieTheater(): ?string
    {
        return $this->projectionEvent->getProjectionRoom()->getMovieTheater();
    }

    #[Groups(['reservation', 'reservation:write'])]
    public function hasRate(): ?bool
    {
        return $this->hasRate;
    }

    public function setHasRate(bool $hasRate): static
    {
        $this->hasRate = $hasRate;

        return $this;
    }
}
