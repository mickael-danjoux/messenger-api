<?php

namespace App\ApiPlatform\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Message;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProcessorInterface<Message, Message|void>
 */
final readonly class MessageProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $processor,
        private Security $security
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $currentUser = $this->security->getUser();
        if ($operation->getClass() === Message::class) {
            $data->setSender($currentUser);
        }
        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
