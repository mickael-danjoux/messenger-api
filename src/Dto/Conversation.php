<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;
use App\Utils\JsonGroups;
use Symfony\Component\Serializer\Attribute\Groups;

class Conversation
{
    #[Groups([JsonGroups::READ_CONVERSATION])]
    #[ApiProperty(example: '/api/users/usr_67dc7729a4sv4')]
    public ?string $recipient = null;

    #[Groups([JsonGroups::READ_CONVERSATION])]
    #[ApiProperty(example: 'John Doe')]
    public ?string $userName = null;

    #[Groups([JsonGroups::READ_CONVERSATION])]
    #[ApiProperty(example: 'Hello John!')]
    public ?string $lastMessageContent = null;

    #[Groups([JsonGroups::READ_CONVERSATION])]
    public ?\DateTimeImmutable $lastMessageDate = null;
}
