<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProjectionFormatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProjectionFormatRepository::class)]
#[ApiResource]
class ProjectionFormat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Groups(["movie", "reservation"])]
    private ?string $projectionFormatName = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $extraCharge = null;

    /**
     * @var Collection<int, ProjectionEvent>
     */
    #[ORM\OneToMany(targetEntity: ProjectionEvent::class, mappedBy: 'format')]
    private Collection $projectionEvents;

    public function __construct() {
        $this->projectionEvents = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    public function __toString()
    {
        return $this->projectionFormatName;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjectionFormatName(): ?string
    {
        return $this->projectionFormatName;
    }

    public function setProjectionFormatName(string $projectionFormatName): static
    {
        $this->projectionFormatName = $projectionFormatName;

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

    public function getExtraCharge(): ?int
    {
        return $this->extraCharge;
    }

    public function setExtraCharge(?int $extraCharge): static
    {
        $this->extraCharge = $extraCharge;

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
            $projectionEvent->setFormat($this);
        }

        return $this;
    }

    public function removeProjectionEvent(ProjectionEvent $projectionEvent): static
    {
        if ($this->projectionEvents->removeElement($projectionEvent)) {
            // set the owning side to null (unless already changed)
            if ($projectionEvent->getFormat() === $this) {
                $projectionEvent->setFormat(null);
            }
        }

        return $this;
    }

}
