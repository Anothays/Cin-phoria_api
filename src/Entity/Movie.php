<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\MovieRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ApiResource]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 50)]
    private ?string $director = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $synopsis = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $casting = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $releasedOn = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $posters = [];

    #[ORM\Column]
    private ?int $minimumAge = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isStaffFavorite = null;

    #[ORM\Column(nullable: true)]
    private ?int $notesTotalPoints = null;

    #[ORM\Column(nullable: true)]
    private ?int $noteTotalVotes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDirector(): ?string
    {
        return $this->director;
    }

    public function setDirector(string $director): static
    {
        $this->director = $director;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(string $synopsis): static
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getCasting(): ?array
    {
        return $this->casting;
    }

    public function setCasting(?array $casting): static
    {
        $this->casting = $casting;

        return $this;
    }

    public function getDuration(): ?\DateTimeInterface
    {
        return $this->duration;
    }

    public function setDuration(\DateTimeInterface $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getReleasedOn(): ?\DateTimeInterface
    {
        return $this->releasedOn;
    }

    public function setReleasedOn(\DateTimeInterface $releasedOn): static
    {
        $this->releasedOn = $releasedOn;

        return $this;
    }

    public function getPosters(): array
    {
        return $this->posters;
    }

    public function setPosters(array $posters): static
    {
        $this->posters = $posters;

        return $this;
    }

    public function getMinimumAge(): ?int
    {
        return $this->minimumAge;
    }

    public function setMinimumAge(int $minimumAge): static
    {
        $this->minimumAge = $minimumAge;

        return $this;
    }

    public function isStaffFavorite(): ?bool
    {
        return $this->isStaffFavorite;
    }

    public function setStaffFavorite(?bool $isStaffFavorite): static
    {
        $this->isStaffFavorite = $isStaffFavorite;

        return $this;
    }

    public function getNotesTotalPoints(): ?int
    {
        return $this->notesTotalPoints;
    }

    public function setNotesTotalPoints(?int $notesTotalPoints): static
    {
        $this->notesTotalPoints = $notesTotalPoints;

        return $this;
    }

    public function getNoteTotalVotes(): ?int
    {
        return $this->noteTotalVotes;
    }

    public function setNoteTotalVotes(?int $noteTotalVotes): static
    {
        $this->noteTotalVotes = $noteTotalVotes;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
