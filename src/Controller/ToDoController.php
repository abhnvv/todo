<?php

namespace App\Controller;

require_once 'Constants.php';

use App\Redis\RedisClient;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\AuthenticationService;
use App\Service\RegistrationService;
use App\Service\SessionService;
use App\Service\AddTaskService;
use App\Service\GetTaskService;
use App\Service\DeleteTaskService;
use App\Service\ArchiveTaskService;
use App\Service\UnArchiveTaskService;
use App\Utils\SessionData;

class ToDoController extends AbstractController
{
    private AuthenticationService $authenticationService;
    private SessionService $sessionService;
    private RegistrationService $registrationService;
    private AddTaskService $addTaskService;
    private GetTaskService $getTaskService;
    private DeleteTaskService $deleteTaskService;
    private ArchiveTaskService $archiveTaskService;
    private UnArchiveTaskService $unArchiveTaskService;
    private $logger;
    private $redis;

    public function __construct(
        AuthenticationService $authenticationService,
        SessionService $sessionService,
        RegistrationService $registrationService,
        AddTaskService $addTaskService,
        GetTaskService $getTaskService,
        DeleteTaskService $deleteTaskService,
        ArchiveTaskService $archiveTaskService,
        UnArchiveTaskService $unArchiveTaskService,
        RedisClient $redisClient,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->redis = $redisClient;
        $this->authenticationService = $authenticationService;
        $this->sessionService = $sessionService;
        $this->registrationService = $registrationService;
        $this->addTaskService = $addTaskService;
        $this->getTaskService = $getTaskService;
        $this->deleteTaskService = $deleteTaskService;
        $this->archiveTaskService = $archiveTaskService;
        $this->unArchiveTaskService = $unArchiveTaskService;
    }

    #[Route('/', name: health_check, methods: ['GET'])]
    public function healthCheck(): Response
    {
        return new Response("Server is running fine and listening to port: 8000");;
    }

    #[Route('/login', name: login, methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        $sessionId = $request->cookies->get(SESSION_ID);
        if ($sessionId) {
            return $this->redirectToRoute(to_do);
        }
        if ($request->isMethod('POST')) {
            $username = $request->request->get(USERNAME);
            $password = $request->request->get(PASSWORD);

            $user = $this->authenticationService->authenticateUser($username, $password);
            $cookie = $this->sessionService->createSession($user->getId());

            $response = $this->redirectToRoute(to_do);
            $response->headers->setCookie($cookie);
            return $response;
        } else return $this->render('/user/login.html.twig');
    }

    #[Route('/register', name: register, methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        $sessionId = $request->cookies->get(SESSION_ID);
        if ($sessionId) {
            return $this->redirectToRoute(to_do);
        } else return $this->registrationService->processRegistrationForm($request);
    }

    #[Route('/logout', name: logout, methods: ['GET'])]
    public function logout(Request $request): Response
    {
        $sessionId = $request->cookies->get(SESSION_ID);
        if (!$sessionId)
            return $this->redirectToRoute(login);
        $response = new Response("User has been logged out!");
        $response->headers->clearCookie(SESSION_ID);
        $redisKey = 'sessionId:' . $sessionId;
        $this->redis->removeKey($redisKey);
        return $response;
    }

    #[Route('/task', name: to_do, methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $sessionId = $request->cookies->get(SESSION_ID);
        if (!$sessionId) {
            return $this->redirectToRoute(login);
        }


        $userId = SessionData::getSessionData($this->redis, $sessionId);
        $this->logger->info('user id:' . $userId);

        if ($userId) {

            $archived = $request->request->get('archived', false);

            $this->logger->info('archived to do: ' . $archived);

            $task = $this->getTaskService->getTask($userId, $archived);

            return $this->render('to_do/index.html.twig', [
                'task' => $task,
                'archived' => $archived
            ]);
        } else {
            return $this->redirectToRoute(login);
        }
    }

    #[Route('/task/add', name: add_task, methods: ['GET', 'POST'])]
    public function addTask(Request $request): Response
    {
        $sessionId = $request->cookies->get(SESSION_ID);

        if ($sessionId) {

            if ($request->isMethod('POST')) {
                return $this->addTaskService->processAddTaskForm($request);
            } else {
                return $this->render('task/addTask.html.twig');
            }
        } else return $this->redirectToRoute(login);
    }

    #[Route('/task/delete/{taskId}', name: delete_task, methods: ['POST'])]
    public function delete(Request $request, string $taskId): Response
    {
        $sessionId = $request->cookies->get(SESSION_ID);
        if (!$sessionId) {
            return $this->redirectToRoute(login);
        }


        $sessionUserId = SessionData::getSessionData($this->redis, $sessionId);

        if ($sessionUserId) {
            $this->logger->info('task id:' . $taskId);
            return $this->deleteTaskService->deleteTask($sessionUserId, $taskId);
        } else {
            return $this->redirectToRoute(login);
        }
    }
    #[Route('/task/archive/{taskId}', name: archive_task, methods: ['POST'])]
    public function archive(Request $request, string $taskId): Response
    {
        $sessionId = $request->cookies->get(SESSION_ID);
        if (!$sessionId) {
            return $this->redirectToRoute(login);
        }


        $sessionUserId = SessionData::getSessionData($this->redis, $sessionId);

        if ($sessionUserId) {
            return $this->archiveTaskService->archiveTask($sessionUserId, $taskId);
        } else {
            return $this->redirectToRoute(login);
        }
    }

    #[Route('/task/unArchive/{taskId}', name: unarchive_task, methods: ['POST'])]
    public function unArchive(Request $request, string $taskId): Response
    {
        $sessionId = $request->cookies->get(SESSION_ID);
        if (!$sessionId) {
            return $this->redirectToRoute(login);
        }

        $sessionUserId = SessionData::getSessionData($this->redis, $sessionId);

        if ($sessionUserId) {
            return $this->unArchiveTaskService->unArchiveTask($sessionUserId, $taskId);
        } else {
            return $this->redirectToRoute(login);
        }
    }
}
