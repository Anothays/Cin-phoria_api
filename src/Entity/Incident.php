<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\IncidentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: IncidentRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['incident']],
    denormalizationContext: ['groups' => ['incident:write']],
    security: "is_granted('ROLE_ADMIN')",
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Put(),
        new Patch()
    ]
)]
class Incident
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["incident", "incident:write"])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(["incident", "incident:write"])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(["incident", "incident:write"])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'incidents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["incident", "incident:write"])]
    private ?ProjectionRoom $projectionRoom = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    public function getProjectionRoom(): ?ProjectionRoom
    {
        return $this->projectionRoom;
    }

    public function setProjectionRoom(?ProjectionRoom $projectionRoom): static
    {
        $this->projectionRoom = $projectionRoom;

        return $this;
    }
}
