<?php


namespace App\Controller;

use Bolt\BoltForms\Event\PostSubmitEvent;
use Bolt\Entity\Content;
use Bolt\Entity\User;
use Bolt\Enum\UserStatus;
use Bolt\Factory\ContentFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserRegistrationController extends AbstractController
{
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
    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->factory = $factory;
        $this->hasher = $passwordHasher;
    }

    /**
     * @Route("/signupStudent", name="signup_student", methods={"POST"})
     */
    public function fetchStudentData(): Response
    {
        // Vang alle post variablen op en stop deze in een array
        $values = [
            'studentnummer' => $this->request->get("signup['studentnummer']"),
            'naam' => $this->request->get("signup['naam']"),
            'email' => $this->request->get("signup['email']"),
            'klas' => $this->request->get("signup['klas']"),
            'adres' => $this->request->get("signup['adres']"),
            'postcode' => $this->request->get("signup['postcode']"),
            'woonplaats' => $this->request->get("signup['woonplaats']"),
            'leeftijd' => $this->request->get("signup['leeftijd']"),
            'wachtwoord' => $this->request->get("signup['wachtwoord']"),
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

    public function upsertUser(array $values): Content
    {
        // Check of er een record bestaat anders creeer er 1
        $record = $this->factory->upsert('antwoorden', [
            'studentnummer' => $values['studentnummer']
        ]);

        $values = [
            'studentnummer' => $values['studentnummer'],
            'naam' => $values['naam'],
            'email' => $values['email'],
            'klas' => $values['klas'],
            'adres' => $values['adres'],
            'postcode' => $values['postcode'],
            'plaats' => $values['plaats'],
            'leeftijd' => $values['leeftijd'],
            'wachtwoord' => $values['wachtwoord'],
        ];

        //Voor elke waarde vul deze in de database
        foreach ($values as $name => $value) {
            if ($record->hasFieldDefined($name)) {
                $record->setFieldValue($name, $value);
            }
        }

        $record->setAuthor($this->getUser());

        $this->factory->save($record);

        return $record;

    }

    public function createUser(array $userData) : User
    {
        $user = new User();

        $user->setDisplayName($userData['naam']);
        $user->setUsername($userData['studentnummer']);
        $user->setEmail($userData['email']);
        $user->setPassword($this->hasher->hashPassword($user, $userData['password']));
//        $user->setStatus(UserStatus::DISABLED);

        return $user;
    }

}
