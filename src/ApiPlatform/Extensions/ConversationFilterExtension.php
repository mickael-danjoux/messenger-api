<?php

namespace App\ApiPlatform\Extensions;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Conversation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Finder\Exception\AccessDeniedException;

final readonly class ConversationFilterExtension  implements QueryCollectionExtensionInterface
{


    public function __construct(
        private Security $security,
    )
    {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (Conversation::class !== $resourceClass) {
            return;
        }
        $user = $this->security->getUser();
        if (!$user) {
            throw new AccessDeniedException();
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->join(sprintf('%s.participants', $rootAlias), 'p')
            ->andWhere('p.user = :current_user')
            ->setParameter('current_user', $user);

    }
}
