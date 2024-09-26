<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Repository\ConversationParticipantRepository;
use App\Utils\JsonGroups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ConversationParticipantRepository::class)]
class ConversationParticipant
{
    #[ORM\Id]
    #[ORM\Column]
    #[ApiProperty(example: 'convp_66dc8729a4ec7')]
    private string $id;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(inversedBy: 'conversationParticipants')]
    #[Groups([JsonGroups::CREATE_CONVERSATION])]
    #[ApiProperty(example: '/api/users/usr_66dc8729a4ec7')]
    private ?user $user = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastMessageReadAt = null;


    public function __construct()
    {
        $this->id = uniqid('convp_');
    }


    public function getId(): string
    {
        return $this->id;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;

        return $this;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getLastMessageReadAt(): ?\DateTimeImmutable
    {
        return $this->lastMessageReadAt;
    }

    public function setLastMessageReadAt(?\DateTimeImmutable $lastMessageReadAt): static
    {
        $this->lastMessageReadAt = $lastMessageReadAt;

        return $this;
    }

    public function markAsRead(): void
    {
        $this->lastMessageReadAt = new \DateTimeImmutable();
    }

}
