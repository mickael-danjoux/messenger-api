<?php

namespace App\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Conversation;
use App\Entity\User;
use App\Repository\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Finder\Exception\AccessDeniedException;

final readonly class ConversationDataProvider implements ProviderInterface
{
    public function __construct(
        private MessageRepository $messageRepository,
        private Security $security
    ) {
    }

    /**
     * @throws \Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Obtenir l’utilisateur courant
        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof User) {
            throw new AccessDeniedException();
        }

        // Récupérer les conversations de l’utilisateur courant
        $conversations = $this->messageRepository->findConversationsForUser($currentUser);

        // Initialiser un tableau pour stocker les résultats
        $conversationDtos = [];
        // Mapper les résultats en objets ConversationDto
        foreach ($conversations as $conversation) {
            $conversationDto = new Conversation();
            $conversationDto->recipient = '/api/users/' . $conversation['userId'];
            $conversationDto->userName = $conversation['firstName'] . ' ' . $conversation['lastName'];
            $conversationDto->lastMessageContent = $conversation['lastMessageContent'];
            $conversationDto->lastMessageDate = $conversation['lastMessageDate'];

            $conversationDtos[] = $conversationDto; // Ajouter chaque ConversationDto au tableau
        }

        // Retourner un tableau de ConversationDto
        return $conversationDtos;
    }
}
