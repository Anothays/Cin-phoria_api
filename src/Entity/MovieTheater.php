<?php

namespace App\Entity;

use App\Repository\MovieTheaterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieTheaterRepository::class)]
class MovieTheater
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    private ?string $theaterName = null;

    #[ORM\Column(length: 60)]
    private ?string $city = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, ProjectionRoom>
     */
    #[ORM\OneToMany(targetEntity: ProjectionRoom::class, mappedBy: 'movieTheater', orphanRemoval: true, cascade: ['persist, remove'])]
    private Collection $projectionRooms;

    public function __construct()
    {
        $this->projectionRooms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTheaterName(): ?string
    {
        return $this->theaterName;
    }

    public function setTheaterName(string $theaterName): static
    {
        $this->theaterName = $theaterName;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

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

    /**
     * @return Collection<int, ProjectionRoom>
     */
    public function getProjectionRooms(): Collection
    {
        return $this->projectionRooms;
    }

    public function addProjectionRoom(ProjectionRoom $projectionRoom): static
    {
        if (!$this->projectionRooms->contains($projectionRoom)) {
            $this->projectionRooms->add($projectionRoom);
            $projectionRoom->setMovieTheater($this);
        }

        return $this;
    }

    public function removeProjectionRoom(ProjectionRoom $projectionRoom): static
    {
        if ($this->projectionRooms->removeElement($projectionRoom)) {
            // set the owning side to null (unless already changed)
            if ($projectionRoom->getMovieTheater() === $this) {
                $projectionRoom->setMovieTheater(null);
            }
        }

        return $this;
    }
}