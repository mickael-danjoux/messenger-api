<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Récupère les dernières conversations de l'utilisateur courant
     * @param User $user
     * @return array
     */
    public function findConversationsForUser(User $user): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select(
                'COALESCE(s.id, r.id) AS userId',  // Récupérer l'ID de l'autre utilisateur
                'COALESCE(s.firstName, r.firstName) AS firstName', // Récupérer le prénom de l'autre utilisateur
                'COALESCE(s.lastName, r.lastName) AS lastName',    // Récupérer le nom de famille de l'autre utilisateur
                'm.content AS lastMessageContent',                 // Récupérer le contenu du dernier message
                'm.createdAt AS lastMessageDate'                   // Récupérer la date du dernier message
            )
            ->leftJoin('m.sender', 's', 'WITH', 's != :user')  // Joindre le sender s'il n'est pas l'utilisateur courant
            ->leftJoin('m.recipient', 'r', 'WITH', 'r != :user') // Joindre le recipient s'il n'est pas l'utilisateur courant
            ->where('m.sender = :user OR m.recipient = :user')   // Limiter les messages à ceux impliquant l'utilisateur courant
            ->andWhere('m.createdAt = (SELECT MAX(m2.createdAt) FROM App\Entity\Message m2 WHERE (m2.sender = :user AND m2.recipient = COALESCE(s.id, r.id)) OR (m2.recipient = :user AND m2.sender = COALESCE(s.id, r.id)))')
            ->setParameter('user', $user)
            ->groupBy('userId, firstName, lastName, m.content, m.createdAt') // Grouper par l'autre utilisateur et message
            ->orderBy('lastMessageDate', 'DESC'); // Trier par date du dernier message

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Message[] Returns an array of Message objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Message
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
