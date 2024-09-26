<?php

namespace App\ApiPlatform\Processors;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ConversationProcessor  implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $processor,
        private Security $security
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $currentUser = $this->security->getUser();
        if ($operation->getClass() === Conversation::class) {
            $participant = new ConversationParticipant();
            $participant->setUser($currentUser);
            $data->addParticipant($participant);
        }
        return $this->processor->process($data, $operation, $uriVariables, $context);

    }
}
