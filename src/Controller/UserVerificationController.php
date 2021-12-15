<?php

namespace App\Controller;

use App\SecretManager;
use Bolt\Controller\TwigAwareController;
use Bolt\Entity\User;
use Bolt\Enum\UserStatus;
use Bolt\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserVerificationController extends TwigAwareController
{
    private SecretManager $secretManager;
    private UserRepository $userRepository;
    private EntityManagerInterface $em;

    public function __construct(
        SecretManager $secretManager,
        UserRepository $userRepository,
        EntityManagerInterface $em)
    {
        $this->secretManager = $secretManager;
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    /**
     * @Route("/user/verify/{secret}", name="verify_user")
     */
    public function verify(string $secret, Request $request): Response
    {
        $email = $request->get('email');

        if (! $this->secretManager->verify($email, $secret)) {
            throw new \Exception('Sorry, this is no longer valid.');
        }

        // activate user
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (! $user instanceof User) {
            throw new \Exception("Sorry, user was not found with email " . $email);
        }

        $user->setStatus(UserStatus::ENABLED);
        $this->em->persist($user);
        $this->em->flush();

        return $this->redirectToRoute('homepage');
    }
}
