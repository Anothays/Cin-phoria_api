<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Repository\ProjectionRoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProjectionRoomRepository::class)]
#[UniqueConstraint('unique_ProjectionRoom', ['title_room', 'movie_theater_id'])]
#[ApiResource(
    normalizationContext: ['groups' => ['projectionRoom']],
    denormalizationContext: ['groups' => ['projectionRoom:write']],
    operations: [
        new GetCollection(),
        new Get(),
    ]
)]
class ProjectionRoom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["projectionRoom", "movie", 'movie:get', "reservation", "movieTheater", "movieTheater:write"])]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    #[Groups(["projectionRoom", "movie", 'movie:get', "reservation", "movieTheater", "movieTheater:write"])]
    private ?string $titleRoom = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, ProjectionRoomSeat>
     */
    #[ORM\OneToMany(targetEntity: ProjectionRoomSeat::class, mappedBy: 'projectionRoom', orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[Groups(["projectionRoom", "projectionRoom:write"])]
    private Collection $projectionRoomSeats;

    #[ORM\ManyToOne(inversedBy: 'projectionRooms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MovieTheater $movieTheater = null;

    /**
     * @var Collection<int, ProjectionEvent>
     */
    #[ORM\OneToMany(targetEntity: ProjectionEvent::class, mappedBy: 'projectionRoom')]
    private Collection $projectionEvents;

    /**
     * @var Collection<int, Incident>
     */
    #[ORM\OneToMany(targetEntity: Incident::class, mappedBy: 'projectionRoom', orphanRemoval: true)]
    #[Groups(["projectionRoom", "projectionRoom:write"])]
    private Collection $incidents;

    public function __construct()
    {
        $this->projectionRoomSeats = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
        $this->projectionEvents = new ArrayCollection();
        $this->incidents = new ArrayCollection();
    }

    public function __toString()
    {
        return "{$this->movieTheater->getTheaterName()} : {$this->titleRoom}";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitleRoom(): ?string
    {
        return $this->titleRoom;
    }

    public function setTitleRoom(string $titleRoom): static
    {
        $this->titleRoom = $titleRoom;

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
     * @return Collection<int, ProjectionRoomSeat>
     */
    public function getProjectionRoomSeats(): Collection
    {
        return $this->projectionRoomSeats;
    }

    public function addProjectionRoomSeat(ProjectionRoomSeat $projectionRoomSeat): static
    {
        if (!$this->projectionRoomSeats->contains($projectionRoomSeat)) {
            $this->projectionRoomSeats->add($projectionRoomSeat);
            $projectionRoomSeat->setProjectionRoom($this);
        }

        return $this;
    }

    public function removeProjectionRoomSeat(ProjectionRoomSeat $projectionRoomSeat): static
    {
        if ($this->projectionRoomSeats->removeElement($projectionRoomSeat)) {
            // set the owning side to null (unless already changed)
            if ($projectionRoomSeat->getProjectionRoom() === $this) {
                $projectionRoomSeat->setProjectionRoom(null);
            }
        }

        return $this;
    }

    public function getMovieTheater(): ?MovieTheater
    {
        return $this->movieTheater;
    }

    public function setMovieTheater(?MovieTheater $movieTheater): static
    {
        $this->movieTheater = $movieTheater;

        return $this;
    }

    /**
     * @return Collection<int, ProjectionEvent>
     */
    public function getProjectionEvents(): Collection
    {
        return $this->projectionEvents;
    }

    public function addProjectionEvent(ProjectionEvent $projectionEvent): static
    {
        if (!$this->projectionEvents->contains($projectionEvent)) {
            $this->projectionEvents->add($projectionEvent);
            $projectionEvent->setProjectionRoom($this);
        }

        return $this;
    }

    public function removeProjectionEvent(ProjectionEvent $projectionEvent): static
    {
        if ($this->projectionEvents->removeElement($projectionEvent)) {
            // set the owning side to null (unless already changed)
            if ($projectionEvent->getProjectionRoom() === $this) {
                $projectionEvent->setProjectionRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Incident>
     */
    public function getIncidents(): Collection
    {
        return $this->incidents;
    }

    public function addIncident(Incident $incident): static
    {
        if (!$this->incidents->contains($incident)) {
            $this->incidents->add($incident);
            $incident->setProjectionRoom($this);
        }

        return $this;
    }

    public function removeIncident(Incident $incident): static
    {
        if ($this->incidents->removeElement($incident)) {
            // set the owning side to null (unless already changed)
            if ($incident->getProjectionRoom() === $this) {
                $incident->setProjectionRoom(null);
            }
        }

        return $this;
    }
}
