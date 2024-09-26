<?php

namespace App\Security\Voter;

use App\Entity\Conversation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class IsOwnerVoter extends Voter
{
    public const IS_OWNER = 'IS_OWNER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute == self::IS_OWNER
            && $subject instanceof Conversation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Conversation $conversation */
        $conversation = $subject;

        if($conversation->hasParticipant($user)){
            return true;
        }

        return false;
    }
}
