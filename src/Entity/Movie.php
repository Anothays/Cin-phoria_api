<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['movie:read']],
    denormalizationContext: ['groups' => ['movie:write']]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'title' => 'partial',
    'movieCategories.categoryName' => 'exact',
])]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['movie:read'])]
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
    // #[Groups(['movie:read'])]
    private ?int $notesTotalPoints = null;

    #[ORM\Column(nullable: true)]
    // #[Groups(['movie:read'])]
    private ?int $noteTotalVotes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTime $updatedAt = null;

    /**
     * @var Collection<int, MovieCategory>
     */
    #[ORM\ManyToMany(targetEntity: MovieCategory::class, mappedBy: 'movies')]
    #[Groups(['movie:read'])]
    private Collection $movieCategories;

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
        $this->movieCategories = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }

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

    #[Groups(['movie:read'])]
    public function getAverageNote(): ?float
    {
        return $this->noteTotalVotes ? $this->notesTotalPoints / $this->noteTotalVotes : null;
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

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, MovieCategory>
     */
    public function getMovieCategories(): Collection
    {
        return $this->movieCategories;
    }

    public function addMovieCategory(MovieCategory $movieCategory): static
    {
        if (!$this->movieCategories->contains($movieCategory)) {
            $this->movieCategories->add($movieCategory);
            $movieCategory->addMovie($this);
        }

        return $this;
    }

    public function removeMovieCategory(MovieCategory $movieCategory): static
    {
        if ($this->movieCategories->removeElement($movieCategory)) {
            $movieCategory->removeMovie($this);
        }

        return $this;
    }
}
