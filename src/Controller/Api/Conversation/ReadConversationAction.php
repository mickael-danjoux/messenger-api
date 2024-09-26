<?php

namespace App\Controller\Api\Conversation;

use App\Entity\Conversation;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReadConversationAction extends AbstractController
{
    public function __invoke(Conversation $conversation): Conversation
    {
        /** @var User $user */
        $user = $this->getUser();
        $participant = $conversation->getParticipant($user);
        $participant->markAsRead();
        return $conversation;
    }
}
