<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use App\Repository\UserRepository;
use App\State\UserStateProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    normalizationContext: ['groups' => ['user']],
    denormalizationContext: ['groups' => ['user:write']],
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_USER")', 
        ),
        new Get(
            security: 'is_granted("ROLE_USER")',
        ),
        new Post(
            processor: UserStateProcessor::class,
        ),
        new Put(
            security: 'is_granted("ROLE_USER")',
            
            // uriTemplate: '/reservations/{id}',
            // controller: CoucouController::class,
            // read: false,
        ),
        new Patch(
            security: 'is_granted("ROLE_USER")'
        )
    ],
)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User extends UserAbstract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user', 'user:write'])]
    private ?int $id = null;

    #[Groups(['user', 'user:write'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[Groups(['user:write'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[Groups(['user','user:write'])]
    private ?string $password = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'user')]
    #[Groups(['user', 'user:write'])]
    private Collection $reservations;

    #[Groups(['user', 'user:write'])]
    private ?string $firstname = null;

    #[Groups(['user', 'user:write'])]
    private ?string $lastname = null;

    #[ORM\Column]
    private bool $isVerified = false;

    public function __construct()
    {
        parent::__construct();
        $this->reservations = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->firstname . " " . $this->lastname;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    // public function getEmail(): ?string
    // {
    //     return $this->email;
    // }

    // public function setEmail(string $email): static
    // {
    //     $this->email = $email;

    //     return $this;
    // }

    // /**
    //  * A visual identifier that represents this user.
    //  *
    //  * @see UserInterface
    //  */
    // public function getUserIdentifier(): string
    // {
    //     return (string) $this->email;
    // }

    // /**
    //  * @see UserInterface
    //  *
    //  * @return list<string>
    //  */
    // public function getRoles(): array
    // {
    //     $roles = $this->roles;
    //     // guarantee every user at least has ROLE_USER
    //     $roles[] = 'ROLE_USER';

    //     return array_unique($roles);
    // }

    // /**
    //  * @param list<string> $roles
    //  */
    // public function setRoles(array $roles): static
    // {
    //     $this->roles = $roles;

    //     return $this;
    // }

    // /**
    //  * @see PasswordAuthenticatedUserInterface
    //  */
    // public function getPassword(): string
    // {
    //     return $this->password;
    // }

    // public function setPassword(string $password): static
    // {
    //     $this->password = $password;

    //     return $this;
    // }

    // /**
    //  * @see UserInterface
    //  */
    // public function eraseCredentials(): void
    // {
    //     // If you store any temporary, sensitive data on the user, clear it here
    //     // $this->plainPassword = null;
    // }

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
            $reservation->setUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getUser() === $this) {
                $reservation->setUser(null);
            }
        }

        return $this;
    }


    // public function getFirstname(): ?string
    // {
    //     return $this->firstname;
    // }

    // public function setFirstname(string $firstname): static
    // {
    //     $this->firstname = $firstname;

    //     return $this;
    // }

    // public function getLastname(): ?string
    // {
    //     return $this->lastname;
    // }

    // public function setLastname(string $lastname): static
    // {
    //     $this->lastname = $lastname;

    //     return $this;
    // }

    public function getFullName(): ?string
    {
        return $this->firstname . " " . $this->lastname;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

}
