<?php

namespace App\Controller;

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
    }

    #[Route('/login', name: 'app_to_do_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        $sessionId = $request->cookies->get('X-Session-ID');
        if ($sessionId) {
            return $this->redirectToRoute('app_to_do');
        }
        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $password = $request->request->get('password');

            $user = $this->authenticationService->authenticateUser($username, $password);
            $cookie = $this->sessionService->createSession($user->getId());

            $response = $this->redirectToRoute('app_to_do');
            $response->headers->setCookie($cookie);
            return $response;
        } else return $this->render('/user/login.html.twig');
    }

    #[Route('/register', name: 'app_to_do_register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        $sessionId = $request->cookies->get('X-Session-ID');
        if ($sessionId) {
            return $this->redirectToRoute('app_to_do');
        } else return $this->registrationService->processRegistrationForm($request);
    }

    #[Route('/logout', name: 'app_to_do_logout', methods: ['GET'])]
    public function logout(Request $request): Response
    {
        $sessionId = $request->cookies->get('X-Session-ID');
        if (!$sessionId)
            return $this->redirectToRoute('app_to_do_login');
        $response = new Response();
        $response->headers->clearCookie('X-Session-ID');
        $redisKey = 'sessionId:' . $sessionId;
        $this->redis->removeKey($redisKey);
        return $response;
    }

    #[Route('/', name: 'app_to_do', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $sessionId = $request->cookies->get('X-Session-ID');
        if (!$sessionId) {
            return $this->redirectToRoute('app_to_do_login');
        }
        $this->logger->info('session id:' . $sessionId);

        $userId = SessionData::getSessionData($this->redis, $sessionId);
        $this->logger->info('user id:' . $userId);

        if ($userId) {

            $archived = $request->request->get('archived', false);

            // $archived = $request->query->get('archived', false); 
            $this->logger->info('archived to do: '. $archived);
  
            $task = $this->getTaskService->getTask($userId, $archived);
    
            return $this->render('to_do/index.html.twig', [
                'task' => $task,
                'archived' => $archived
            ]);
        } else {
            return $this->redirectToRoute('app_to_do_login');
        }
    }

    #[Route('/addTask', name: 'app_to_do_add_task', methods: ['GET', 'POST'])]
    public function addTask(Request $request): Response
    {
        $sessionId = $request->cookies->get('X-Session-ID');
        $this->logger->info('session id:' . $sessionId);
        if ($sessionId) {

            if ($request->isMethod('POST')) {
                return $this->addTaskService->processAddTaskForm($request);
            } else {
                return $this->render('task/addTask.html.twig');
            }
        } else return $this->redirectToRoute('app_to_do_login');
    }

    #[Route('/deleteTask/{taskId}', name: 'app_to_do_delete_task', methods: ['POST'])]
    public function delete(Request $request, string $taskId): Response
    {
        $sessionId = $request->cookies->get('X-Session-ID');
        if (!$sessionId) {
            return $this->redirectToRoute('app_to_do_login');
        }

        $this->logger->info('session id:' . $sessionId);
        $sessionUserId = SessionData::getSessionData($this->redis, $sessionId);

        $this->logger->info('session userid:' . $sessionUserId);
        if ($sessionUserId) {
            $this->logger->info('task id:' . $taskId);
            return $this->deleteTaskService->deleteTask($sessionUserId, $taskId);
        } else {
            return $this->redirectToRoute('app_to_do_login');
        }
    }
    #[Route('/task/archive/{taskId}', name: 'app_to_do_archive_task', methods: ['POST'])]
    public function archive(Request $request, string $taskId): Response
    {
        $sessionId = $request->cookies->get('X-Session-ID');
        if (!$sessionId) {
            return $this->redirectToRoute('app_to_do_login');
        }

        $this->logger->info('session id:' . $sessionId);
        $sessionUserId = SessionData::getSessionData($this->redis, $sessionId);

        $this->logger->info('session userid:' . $sessionUserId);
        if ($sessionUserId) {
            $this->logger->info('task id:' . $taskId);
            return $this->archiveTaskService->archiveTask($sessionUserId, $taskId);
        } else {
            return $this->redirectToRoute('app_to_do_login');
        }
    }
}
