<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\ApiPlatform\Processor\MessageProcessor;
use App\ApiPlatform\Provider\ConversationDataProvider;
use App\Dto\Conversation;
use App\Repository\MessageRepository;
use App\Utils\JsonGroups;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(
    operations:[
        new Get(),
        new GetCollection(
            uriTemplate: "/conversations",
            paginationEnabled: false,
            normalizationContext: ['groups' => [JsonGroups::READ_CONVERSATION]],
            output: Conversation::class,
            provider: ConversationDataProvider::class
        ),
        new Post(processor: MessageProcessor::class),
    ],
    normalizationContext: ['groups' => [JsonGroups::READ_MESSAGE]],
    denormalizationContext: ['groups' => [JsonGroups::CREATE_MESSAGE]],
)]
class Message
{
    #[ORM\Id]
    #[ORM\Column]
    #[Groups([JsonGroups::READ_MESSAGE])]
    #[ApiProperty(example: 'msg_66dc8729a4ec7')]
    private string $id;

    #[ORM\Column]
    #[Groups([JsonGroups::READ_MESSAGE])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([JsonGroups::READ_MESSAGE, JsonGroups::CREATE_MESSAGE])]
    #[ApiProperty(example: 'Hello John!')]
    #[Assert\NotBlank]
    private ?string $content = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([JsonGroups::READ_MESSAGE])]
    #[ApiProperty(example: '/api/users/usr_67dc7729a4sv4')]
    private ?User $sender = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([JsonGroups::READ_MESSAGE, JsonGroups::CREATE_MESSAGE])]
    #[ApiProperty(example: '/api/users/usr_66dc8729a4ec7')]
    #[Assert\NotBlank]
    private ?User $recipient = null;

    public function __construct()
    {
        $this->id = uniqid('msg_');
        $this->createdAt = new \DateTimeImmutable();
    }


    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }


    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(?User $recipient): static
    {
        $this->recipient = $recipient;

        return $this;
    }
}
