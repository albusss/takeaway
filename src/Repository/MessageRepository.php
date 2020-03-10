<?php

namespace App\Repository;

use App\Entity\Message;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    /**
     * MessageRepository constructor.
     *
     * @param \Symfony\Bridge\Doctrine\RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @param string $idempotencyKey
     *
     * @return Message|null
     */
    public function getByIdempotencyKey(string $idempotencyKey): ?Message
    {
        return $this->findOneBy(['idempotencyKey' => $idempotencyKey]);
    }

    /**
     * @param string|null $status
     * @param \DateTime|null $from
     * @param int|null $limit
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getMessagesQueryBuilderForMainPage(
        ?string $status = null,
        ?DateTime $from = null,
        ?int $limit = 50
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('m');

        if ($status) {
            $qb->andWhere('m.status = :status');
            $qb->setParameter('status', $status);
        }

        if ($from) {
            $qb->andWhere('m.created > :from');
            $qb->setParameter('from', $from);
        }

        $qb->orderBy('m.created', 'DESC');
        $qb->setMaxResults($limit);

        return $qb;
    }

    /**
     * @param \DateTime|null $from
     * @param int|null $limit
     *
     * @return Message[]
     */
    public function getNotSuccessMessagesForMainPage(?DateTime $from, ?int $limit = 50): array
    {
        $qb = $this->getMessagesQueryBuilderForMainPage(null, $from, $limit);

        $qb->andWhere('m.status != :status');
        $qb->setParameter('status', Message::STATUS_SUCCESS);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \DateTime|null $from
     * @param int|null $limit
     *
     * @return Message[]
     */
    public function getSuccessMessagesForMainPage(?\DateTime $from, ?int $limit = 50): array
    {
        $qb = $this->getMessagesQueryBuilderForMainPage(Message::STATUS_SUCCESS, $from, $limit);

        return $qb->getQuery()->getResult();
    }
}
