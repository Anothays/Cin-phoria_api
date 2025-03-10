<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use App\Enum\ProjectionEventLanguage;
use App\Repository\ProjectionEventRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: ProjectionEventRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
    ]
)]
class ProjectionEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['movie', 'movie:get', 'reservation', 'reservation:write', 'projectionEvent'])]
    private ?int $id = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'projectionEvent', orphanRemoval: true)]
    private Collection $reservations;

    #[ORM\Column(enumType: ProjectionEventLanguage::class, length: 20)]
    #[Groups(['movie', 'movie:get', 'reservation', 'reservation:write'])]
    private ?ProjectionEventLanguage $language = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'H:i'])]
    #[Groups(['movie', 'movie:get', 'reservation', 'reservation:write'])]
    private ?\DateTimeInterface $beginAt = null;

    #[ORM\ManyToOne(inversedBy: 'projectionEvents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['movie', 'movie:get', 'reservation', 'reservation:write'])]
    private ?ProjectionFormat $format = null;

    #[ORM\ManyToOne(inversedBy: 'projectionEvents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['reservation'])]
    private ?Movie $movie = null;

    #[ORM\ManyToOne(inversedBy: 'projectionEvents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['movie', 'movie:get', 'reservation', 'reservation:write'])]
    private ?ProjectionRoom $projectionRoom = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;
    

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTime();
        $this->reservations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->movie->getTitle();
    }

    #[Groups(['reservation', 'reservation:write'])]
    public function getReservedSeats(): array
    {
        $reservations = $this->reservations->getValues();
        $seats = [];
        foreach ($reservations as $reservation) {
            array_push($seats, ...$reservation->getSeats()->getValues());
        };
        return array_map(fn (ProjectionRoomSeat $seat) => $seat->getId(), $seats);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLanguage(): ?ProjectionEventLanguage
    {
        return $this->language;
    }

    public function setLanguage(ProjectionEventLanguage $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getBeginAt(): ?\DateTimeInterface
    {
        return $this->beginAt;
    }

    #[Context([DateTimeNormalizer::FORMAT_KEY => 'd/m/Y'])]
    #[Groups(['movie', 'movie:get', 'reservation', 'reservation:write'])]
    public function getDate(): ?\DateTimeInterface
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTimeInterface $beginAt): static
    {
        $this->beginAt = $beginAt;

        return $this;
    }

    #[Context([DateTimeNormalizer::FORMAT_KEY => 'H:i'])]
    #[Groups(['movie', 'movie:get', 'reservation', 'reservation:write'])]
    public function getEndAt(): ?\DateTimeInterface
    {
        return (new DateTime($this->beginAt->format('Y-m-d H:i:s')))->modify("+{$this->movie->getDurationInMinutes()} minutes");
    }

    public function getFormat(): ?ProjectionFormat
    {
        return $this->format;
    }

    public function setFormat(?ProjectionFormat $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    public function setMovie(?Movie $movie): static
    {
        $this->movie = $movie;

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
            $reservation->setProjectionEvent($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getProjectionEvent() === $this) {
                $reservation->setProjectionEvent(null);
            }
        }

        return $this;
    }

    public function getAvailableSeatsCount(): int
    {
        return count($this->getAvailableSeats());
    }

    public function getSoldSeatsCount(): int
    {
        return count($this->getSoldSeats());
    }


    /**
     * @return Collection<int, ProjectionRoomSeat>
     */
    // #[Groups(['reservation', 'reservation:write'])]
    public function getAvailableSeats(): Collection
    {
        $allRoomSeats = $this->projectionRoom->getProjectionRoomSeats();
        $reservedSeats = new ArrayCollection(
            array_merge(
                ...array_map(
                    fn($reservation) => $reservation->getSeats()->toArray(), 
                    $this->reservations->toArray()
                )
            )
        );
        // Filtrer les sièges disponibles (ceux qui ne sont pas réservés)
        $availableSeats = $allRoomSeats->filter(function ($seat) use ($reservedSeats) {
            return !$reservedSeats->contains($seat);
        });

        return $availableSeats;
    }

     /**
     * @return Collection<int, ProjectionRoomSeat>
     */
    public function getSoldSeats(): Collection
    {
        $reservedSeats = new ArrayCollection(
            array_merge(
                ...array_map(
                    fn($reservation) => $reservation->getSeats()->toArray(), 
                    array_filter($this->reservations->toArray(), fn($reservation) => $reservation->isPaid())
                )
            )
        );

        return $reservedSeats;
    }

    #[Groups(['reservation', 'reservation:write'])]
    public function getAllSeats(): Collection
    {
        return $this->projectionRoom->getProjectionRoomSeats();
    }

    #[Groups(["movie", 'movie:get', 'reservation', 'reservation:write'])]
    public function getMovieTheater(): ?MovieTheater
    {
        return $this->getProjectionRoom()->getMovieTheater();
    }

    #[Groups(["movie", "movie:get", 'reservation', 'reservation:write'])]
    public function hasSeatsForReducedMobility(): bool
    {
        return $this->projectionRoom->getProjectionRoomSeats()->exists(function($key, $value) {
            return $value->isForReducedMobility();
        });
    }
}


