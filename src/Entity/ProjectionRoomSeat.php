<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProjectionRoomSeatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity(repositoryClass: ProjectionRoomSeatRepository::class)]
#[ApiResource]
#[UniqueConstraint('unique_seatRow_seatNumber_ProjectionRoom', ['seat_row', 'seat_number', 'projection_room_id'])]
class ProjectionRoomSeat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1)]
    private ?string $seatRow = null;

    #[ORM\Column]
    private ?int $seatNumber = null;

    #[ORM\Column]
    private ?bool $isForReducedMobility = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'projectionRoomSeats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProjectionRoom $projectionRoom = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\ManyToMany(targetEntity: Reservation::class, mappedBy: 'seats')]
    private Collection $reservations;

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
        $this->reservations = new ArrayCollection();
    }

    public function getRowAndNumberSeat(): string
    {
        return $this->seatRow . $this->seatNumber;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeatRow(): ?string
    {
        return $this->seatRow;
    }

    public function setSeatRow(string $seatRow): static
    {
        $this->seatRow = $seatRow;

        return $this;
    }

    public function getSeatNumber(): ?int
    {
        return $this->seatNumber;
    }

    public function setSeatNumber(int $seatNumber): static
    {
        $this->seatNumber = $seatNumber;

        return $this;
    }

    public function isForReducedMobility(): ?bool
    {
        return $this->isForReducedMobility;
    }

    public function setForReducedMobility(bool $isForReducedMobility): static
    {
        $this->isForReducedMobility = $isForReducedMobility;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getProjectionRoom(): ?ProjectionRoom
    {
        return $this->projectionRoom;
    }

    public function setProjectionRoom(?ProjectionRoom $projectionRoom): static
    {
        $this->projectionRoom = $projectionRoom;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->addSeat($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            $reservation->removeSeat($this);
        }

        return $this;
    }
}
