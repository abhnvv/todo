<?php

namespace App\Service;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Psr\Log\LoggerInterface;

class GetTaskService
{
    private $entityManager;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    public function getTask(string $userId, bool $isArchived): array
    {
        $this->logger->info('archived: '. $isArchived);
        
        $taskRepository = $this->entityManager->getRepository(Task::class);
        $task = $taskRepository->createQueryBuilder('t')
        ->where('t.userId = :userId')
        ->andWhere('t.isArchived = :isArchived')
        ->setParameter('userId', $userId)
        ->setParameter('isArchived', $isArchived)
        ->getQuery()
        ->getResult();
        return $task;
    }
}
