<?php


namespace App\Controller;

use Bolt\BoltForms\Event\PostSubmitEvent;
use Bolt\Entity\Content;
use Bolt\Entity\User;
use Bolt\Enum\UserStatus;
use Bolt\Factory\ContentFactory;
use Bolt\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    private $request;

    /** @var UserPasswordHasherInterface */
    private $hasher;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        ContentFactory $factory,
        RequestStack $requestStack,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->factory = $factory;
        $this->hasher = $passwordHasher;
        $this->entityManager = $em;
    }

    public function upsertUser(array $values, User $user): Content
    {
        // Check of er een record bestaat anders creeer er 1
        $record = $this->factory->upsert('studenten', [
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
            'wachtwoord' => $values['password'],
        ];

        //Voor elke waarde vul deze in de database
        foreach ($values as $name => $value) {
            if ($record->hasFieldDefined($name)) {
                $record->setFieldValue($name, $value);
            }
        }

        $record->setAuthor($user);

        $this->factory->save($record);

        return $record;

    }

    public function createUser(array $userData) : User
    {
        $user = UserRepository::factory($userData['naam'], $userData['studentnummer'], $userData['email']);
        $user->setRoles(['ROLE_STUDENT']);

        $hashedPassword = $this->hasher->hashPassword($user, $userData['password']);

        $user->setPassword($hashedPassword);
        $user->setStatus(UserStatus::DISABLED);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->upsertUser($userData, $user);

        return $user;
    }

}
