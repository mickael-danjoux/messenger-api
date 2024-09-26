<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiPlatform\Processors\ConversationProcessor;
use App\ApiPlatform\Providers\ConversationProvider;
use App\Controller\Api\Conversation\ReadConversationAction;
use App\Repository\ConversationRepository;
use App\Utils\JsonGroups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            processor: ConversationProcessor::class
        ),
        new Patch(
            uriTemplate: '/conversations/{id}/read',
            controller: ReadConversationAction::class,
            security:  "is_granted('IS_OWNER', object)"
        ),
        new Get(security:  "is_granted('IS_OWNER', object)"),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => [JsonGroups::READ_CONVERSATION, JsonGroups::READ_MESSAGE]],
    denormalizationContext: ['groups' => [JsonGroups::CREATE_CONVERSATION]],
    provider: ConversationProvider::class,
)]
class Conversation
{
    #[ORM\Id]
    #[ORM\Column]
    #[ApiProperty(example: 'conv_66dc8729a4ec7')]
    #[Groups([JsonGroups::READ_CONVERSATION])]
    private ?string $id;

    /**
     * @var Collection<int, ConversationParticipant>
     */
    #[ORM\OneToMany(targetEntity: ConversationParticipant::class, mappedBy: 'conversation', cascade: ['persist'])]
    #[Groups([JsonGroups::CREATE_CONVERSATION])]
    private Collection $participants;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversation', orphanRemoval: true)]
    private Collection $messages;

    #[Groups([JsonGroups::READ_CONVERSATION])]
    public bool $hasUnreadMessages = false;

    public function __construct()
    {
        $this->id = uniqid('conv_');
        $this->participants = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }


    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return Collection<int, ConversationParticipant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(ConversationParticipant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setConversation($this);
        }

        return $this;
    }

    public function removeParticipant(ConversationParticipant $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if ($participant->getConversation() === $this) {
                $participant->setConversation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }

        return $this;
    }

    #[Groups([JsonGroups::READ_CONVERSATION])]
    public function getLastMessage(): ?Message
    {
        return $this->messages->last() ?:
            null;
    }

    public function hasParticipant(User $user): bool
    {
        return !!$this->getParticipant($user);

    }

    public function getParticipant(User $user): ?ConversationParticipant
    {
        foreach ($this->participants as $participant) {
            if ($participant->getUser() === $user) {
                return $participant;
            }
        }
        return null;
    }

}
