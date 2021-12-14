<?php


namespace App\Controller;

use Bolt\BoltForms\Event\PostSubmitEvent;
use Bolt\Entity\Content;
use Bolt\Entity\User;
use Bolt\Factory\ContentFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserRegistrationController extends AbstractController
{
    /** @var PostSubmitEvent */
    private PostSubmitEvent $postEvent;

    /** @var ContentFactory */
    private ContentFactory $factory;

    /** @var Request */
    private Request $request;

    /** @var UserPasswordHasherInterface */
    private $passwordHasher;

    public function __construct(
        ContentFactory $factory,
        RequestStack $requestStack,
        UserPasswordHasherInterface $passwordHasher,
        PostSubmitEvent $postEvent
    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->factory = $factory;
        $this->hasher = $passwordHasher;
        $this->postEvent = $postEvent;
    }

    /**
     * @Route("/signupStudent", name="signup_student", methods={"POST"})
     */
    public function fetchStudentData(): Response
    {
        // Vang alle post variablen op en stop deze in een array
        $values = [
            'naam' => $this->request->get("naam"),
            'studentnummer' => $this->request->get("studentnummer"),
            'kilometer' => $this->request->get("kilometer"),
            'minuten' => $this->request->get("minuten"),
            'vervoer' => $this->request->get("vervoer"),
            'startles' => $this->request->get("startLes"),
            'eindles' => $this->request->get("eindLes"),
            'extra' => $this->request->get("extra") ?? ''
        ];

        $user = [
            'email' => $this->request->get("email"),
            'name' => $this->request->get("naam"),
            'password' => $this->request->get("wachtwoord"),
            'studentnummer' => $this->request->get("studentnummer")
        ];

        // Loop over de values en 'upsert' (update or insert) deze in de database
        $this->upsertUser($values);
        $this->createUser($user);

        return new Response('OK');
    }

    private function upsertUser(array $values): Content
    {
        // Check of er een record bestaat anders creeer er 1
        $record = $this->factory->upsert('antwoorden', [
            'studentnummer' => $values['studentnummer']
        ]);

        $values = [
            'studentnummer' => $values['studentnummer'],
            'naam' => $values['naam'],
            'email' => $values['kilometer'],
            'klas' => $values['minuten'],
            'vervoer' => $values['vervoer'],
            'startles' => $values['startles'],
            'eindles' => $values['eindles'],
            'extra' => $values['extra'] ?? ''
        ];

        //Voor elke waarde vul deze in de database
        foreach ($values as $name => $value) {
            $record->setFieldValue($name, $value);
        }

        $record->setAuthor($this->getUser());

        $this->factory->save($record);

        return $record;

    }

    private function createUser(array $userData) : User
    {
        $user = new User();

        $user->setDisplayName($userData['naam']);
        $user->setUsername($userData['studentnummer']);
        $user->setEmail($userData['email']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $userData['password']));

        return $user;
    }

}
