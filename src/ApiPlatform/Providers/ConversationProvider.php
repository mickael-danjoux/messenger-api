<?php

namespace App\ApiPlatform\Providers;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Conversation;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ConversationProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $itemProvider,
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private ProviderInterface $collectionProvider,
        #[Autowire(service: 'security.helper')]
        private Security $security
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {

        if ($operation instanceof CollectionOperationInterface) {
            $conversations = $this->collectionProvider->provide($operation, $uriVariables, $context);
            foreach ($conversations as $conversation) {
                $conversation->hasUnreadMessages = $this->hasUnreadMessages($conversation);
            }
            return $conversations;
        } else {
            $conversation = $this->itemProvider->provide($operation, $uriVariables, $context);
            $conversation->hasUnreadMessages = $this->hasUnreadMessages($conversation);
            return $conversation;
        }
    }

    private function hasUnreadMessages(Conversation $conversation): bool
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        $participant = $conversation->getParticipant($currentUser);
        $lastMessage = $conversation->getLastMessage();
        if($lastMessage){
            $isLastSender = $lastMessage->getSender() === $currentUser;
            if (!$isLastSender && $participant->getLastMessageReadAt() < $lastMessage->getCreatedAt()) {
                return true;
            }
        }

        return false;
    }
}
