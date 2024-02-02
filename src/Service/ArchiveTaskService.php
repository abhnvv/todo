<?php

namespace App\Service;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\DeleteTaskService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Config\Framework\HttpClient\DefaultOptions\RetryFailedConfig;

class ArchiveTaskService
{
    private $entityManager;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
    }

    public function archiveTask(string $userId, string $taskId): Response
    {
        $taskRepository = $this->entityManager->getRepository(Task::class);
        $task = $taskRepository->find($taskId);
        if (!$task) {
            throw new AuthenticationException('Invalid Task Id');
        }
        if($task->getUserId() === $userId)
        {
            $task->setIsArchived(true);
            $this->entityManager->flush();
            return new RedirectResponse($this->urlGenerator->generate('app_to_do'));
        }
        else throw new AuthenticationException('Cannot archive other user task!');
    }
}
