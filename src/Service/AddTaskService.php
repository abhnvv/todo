<?php
namespace App\Service;

require_once 'Constants.php';


use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Redis\RedisClient;
use Twig\Environment as TwigEnvironment;
use App\Utils\SessionData;


class AddTaskService
{
    private EntityManagerInterface $entityManager;
    private UrlGeneratorInterface $urlGenerator;
    private TwigEnvironment $twig;
    private RedisClient $redis;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, RedisClient $redis, TwigEnvironment $twig)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
        $this->redis = $redis;
    }

    public function processAddTaskForm(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            
            $sessionId = $request->cookies->get(SESSION_ID);
            $userId = SessionData::getSessionData($this->redis, $sessionId);
            
            if ($userId) {

                $title = $request->request->get('title');
                $description = $request->request->get('description');
                $isArchived = $request->request->get('isArchived');
                $dueDate = $request->request->get('dueDate');
                $currentDateTime = new \DateTime();
                $createdAt = $currentDateTime->format('Y-m-d H:i:s');

                $task = $this->createTask($userId, $title, $description, $isArchived, $dueDate, $createdAt);

                $this->entityManager->persist($task);
                $this->entityManager->flush();

                return new RedirectResponse($this->urlGenerator->generate(to_do));
            } else {
                return new RedirectResponse($this->urlGenerator->generate(login));
            }
        }

        return new Response($this->twig->render('user/index.html.twig'));
    }

    private function createTask($userId, $title, $description, $isArchived, $dueDate, $createdAt): Task
    {
        $task = new Task();
        $task->setUserId($userId);
        $task->setTitle($title);
        $task->setDescription($description);
        $task->setIsArchived($isArchived);
        $task->setDueDate($dueDate);
        $task->setCreatedAt($createdAt);

        return $task;
    }
}
