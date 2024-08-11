<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProjectionRoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectionRoomRepository::class)]
#[ApiResource]
class ProjectionRoom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 2)]
    private ?string $titleRoom = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, ProjectionRoomSeat>
     */
    #[ORM\OneToMany(targetEntity: ProjectionRoomSeat::class, mappedBy: 'projectionRoom', orphanRemoval: true, cascade: ['persist, remove'])]
    private Collection $projectionRoomSeats;

    #[ORM\ManyToOne(inversedBy: 'projectionRooms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MovieTheater $movieTheater = null;

    public function __construct()
    {
        $this->projectionRoomSeats = new ArrayCollection();
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
}
