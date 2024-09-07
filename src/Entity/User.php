<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiPlatform\Processor\UserPasswordHasherProcessor;
use App\Repository\UserRepository;
use App\Utils\JsonGroups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity('email')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(denormalizationContext: ['groups' => [JsonGroups::CREATE_USER]],
            validationContext: ['groups' => ['Default', JsonGroups::CREATE_USER]],
            processor: UserPasswordHasherProcessor::class),
        new Get(),
        new Patch(
            denormalizationContext: ['groups' => [JsonGroups::UPDATE_USER]],
            processor: UserPasswordHasherProcessor::class),
        new Delete(),
    ],
    normalizationContext: ['groups' => [JsonGroups::READ_USER]],
    denormalizationContext: ['groups' => [JsonGroups::CREATE_USER, JsonGroups::UPDATE_USER]],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{


    #[ORM\Id]
    #[ORM\Column]
    #[Groups([JsonGroups::READ_USER])]
    private string $id;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups([JsonGroups::READ_USER, JsonGroups::CREATE_USER])]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[ApiProperty(example: 'john.doe@example.com')]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[Groups([JsonGroups::CREATE_USER, JsonGroups::UPDATE_USER])]
    #[Assert\NotBlank(groups: [JsonGroups::CREATE_USER])]
    #[ApiProperty(example: 'Azerty1%')]
    private ?string $plainPassword = null;

    #[ORM\Column]
    #[Groups([JsonGroups::READ_USER])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups([JsonGroups::READ_USER, JsonGroups::CREATE_USER, JsonGroups::UPDATE_USER])]
    #[ApiProperty(example: 'John')]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups([JsonGroups::READ_USER, JsonGroups::CREATE_USER, JsonGroups::UPDATE_USER])]
    #[ApiProperty(example: 'DOE')]
    private ?string $lastName = null;

    public function __construct()
    {
        $this->id = uniqid('usr_');
        $this->createdAt = new \DateTimeImmutable();
    }


    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @return list<string>
     * @see UserInterface
     *
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = ucfirst($firstName);

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = mb_strtoupper($lastName);

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

}
