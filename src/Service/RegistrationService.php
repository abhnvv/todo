<?php
namespace App\Service;

require_once('Constants.php');

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as TwigEnvironment;


class RegistrationService
{
    private EntityManagerInterface $entityManager;
    private UrlGeneratorInterface $urlGenerator;
    private TwigEnvironment $twig;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, TwigEnvironment $twig)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    public function processRegistrationForm(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $password = $request->request->get('password');

            $user = $this->createUser($username, $password);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return new RedirectResponse($this->urlGenerator->generate(login));
        }

        return new Response($this->twig->render('user/register.html.twig'));
    }

    private function createUser(string $username, string $password): User
    {
        $user = new User();
        $user->setUsername($username);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $user->setPassword($hash);
        return $user;
    }
}
