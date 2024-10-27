<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\QueryParameter;
use App\Controller\CoucouController;
use App\Dto\RateMovieDto;
use App\Repository\MovieRepository;
use App\State\MovieStateProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Metadata\UriVariable;
use App\Controller\RateMovieController;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
#[IsGranted("ROLE_USER")]
#[ApiResource(
    normalizationContext: ['groups' => ['movie']],
    denormalizationContext: ['groups' => ['movie', 'movie:write']],
    operations: [
        // new Post(
        //     name: 'coucou',
        //     uriTemplate: '/coucou/{id}',
        //     controller: CoucouController::class
        // ),
        // new GetCollection(
        //     name: 'lol',
        //     uriTemplate: '/lol',
        //     controller: CoucouController::class,
        //     // openapi:
        // ) 
    ]
)]
#[Get()]
#[GetCollection()]
#[Post(security: "is_granted('ROLE_ADMIN')")]
#[Put(security: "is_granted('ROLE_ADMIN')")]
#[Patch(
    // controller: RateMovieController::class
    // name: "api_rate_movie",
    // uriTemplate: "/movies/rate",
    // processor: MovieStateProcessor::class,
    // input: RateMovieDto::class,
    // uriVariables: [
    //     'reservation_id' => new Link(
    //         fromClass: Reservation::class,
    //         fromProperty: 'id',
    //         identifiers: ['id'],
    //     )
    // ],
)]
#[Delete(security: "is_granted('ROLE_ADMIN')")]
#[Vich\Uploadable]
// #[QueryParameter(key: ':property', filter: SearchFilter::class)]
#[ApiFilter(BooleanFilter::class, properties: ['isStaffFavorite'])]
#[ApiFilter(SearchFilter::class, properties: [
    'projectionEvents.beginAt' => 'partial',
    'projectionEvents.projectionRoom.movieTheater.theaterName' => 'exact',
    'movieCategories.categoryName' => 'exact',
    'createdAt' => 'exact',
    'title' => 'exact',
    ]
)]
// #[ApiFilter(DateFilter::class, properties: [
//     'projectionEvents.beginAt' => 'exact', // Ajoutez ce filtre pour la date
// ])]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["movie", 'reservation'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(["movie", 'reservation'])]
    private ?string $title = null;

    #[ORM\Column(length: 50)]
    #[Groups(["movie", 'reservation'])]
    private ?string $director = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["movie", 'reservation'])]
    private ?string $synopsis = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    #[Groups(["movie", 'reservation'])]
    private ?array $casting = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(["movie", 'reservation'])]
    private ?\DateTimeInterface $releasedOn = null;

    #[ORM\Column(type: Types::ARRAY)]
    #[Groups(["movie", 'reservation'])]
    private array $posters = [];

    #[ORM\Column]
    #[Groups(["movie", 'reservation'])]
    private ?int $minimumAge = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["movie", 'reservation'])]
    private ?bool $staffFavorite = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["movie", 'reservation'])]
    private ?int $notesTotalPoints = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["movie", 'reservation'])]
    private ?int $noteTotalVotes = null;

    #[ORM\Column]
    #[Groups(["movie", 'reservation'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTime $updatedAt = null;

    /**
     * @var Collection<int, MovieCategory>
     */
    #[ORM\ManyToMany(targetEntity: MovieCategory::class, mappedBy: 'movies')]
    #[Groups(["movie", 'reservation'])]
    private Collection $movieCategories;

    /**
     * @var Collection<int, ProjectionEvent>
     */
    #[ORM\OneToMany(targetEntity: ProjectionEvent::class, mappedBy: 'movie', orphanRemoval: true)]
    #[Groups(["movie"])]
    private Collection $projectionEvents;

    #[ORM\Column]
    #[Groups(["movie", 'reservation'])]
    private ?int $durationInMinutes = null;

    #[Vich\UploadableField(mapping: 'movies', fileNameProperty: 'coverImageName', size: 'coverImageSize')]
    private ?File $coverImageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["movie", 'reservation'])]
    private ?string $coverImageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $coverImageSize = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'movie')]
    private Collection $comments;

    

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
        $this->movieCategories = new ArrayCollection();
        $this->projectionEvents = new ArrayCollection();
        $this->comments = new ArrayCollection();
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

    #[Groups(["movie", 'reservation'])]
    public function getStaffFavorite(): ?bool
    {
        return $this->staffFavorite;
    }

    public function setStaffFavorite(?bool $staffFavorite): static
    {
        $this->staffFavorite = $staffFavorite;

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

    #[Groups(['movie', 'reservation'])]
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
            $projectionEvent->setMovie($this);
        }

        return $this;
    }

    public function removeProjectionEvent(ProjectionEvent $projectionEvent): static
    {
        if ($this->projectionEvents->removeElement($projectionEvent)) {
            // set the owning side to null (unless already changed)
            if ($projectionEvent->getMovie() === $this) {
                $projectionEvent->setMovie(null);
            }
        }

        return $this;
    }

    public function getDurationInMinutes(): ?int
    {
        return $this->durationInMinutes;
    }

    public function setDurationInMinutes(int $durationInMinutes): static
    {
        $this->durationInMinutes = $durationInMinutes;

        return $this;
    }

    public function getCoverImageName(): ?string
    {
        return $this->coverImageName;
    }

    public function setCoverImageName(?string $coverImageName): static
    {
        $this->coverImageName = $coverImageName;

        return $this;
    }

    public function setCoverImageFile(?File $coverImageFile = null): void
    {
        $this->coverImageFile = $coverImageFile;

        if (null !== $coverImageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTime();
        }
    }

    public function getCoverImageFile(): ?File
    {
        return $this->coverImageFile;
    }

    public function setCoverImageSize(?int $coverImageSize): void
    {
        $this->coverImageSize = $coverImageSize;
    }

    public function getCoverImageSize(): ?int
    {
        return $this->coverImageSize;
    }


    public function getMovieTheatersWithProjectionEvents(): Collection
    {
        $callback = function( ProjectionEvent $item) {
            return $item->getMovieTheater();
        };
        // $movieTheaters = array_map($callback, [...$this->projectionEvents]);
        $movieTheaters = new ArrayCollection();
        
        foreach ($this->projectionEvents as $projectionEvent) {
            $movieTheater = $projectionEvent->getMovieTheater();
            if (!$movieTheaters->contains($movieTheater)) {
                $movieTheaters->add($movieTheater);
            }
        }

        return $movieTheaters;

    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setMovie($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getMovie() === $this) {
                $comment->setMovie(null);
            }
        }

        return $this;
    }

    


}
