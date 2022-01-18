<?php


namespace App;


use Bolt\BoltForms\Event\PostSubmitEvent;
use Bolt\BoltForms\EventSubscriber\Redirect;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Controller\UserRegistrationController;


class PostSubmitSubscriber implements EventSubscriberInterface
{
    private UserRegistrationController $registrationController;

    public function __construct(UserRegistrationController $registrationController)
    {
        $this->registrationController = $registrationController;
    }

    public static function getSubscribedEvents()
    {
        return [
            PostSubmitEvent::NAME => ['onPostSubmit']
        ];
    }


    // We hebben deze functie nodig om de gegevens van het formulier op te vangen voor de gebruiker
    public function onPostSubmit(PostSubmitEvent $event): void
    {
        $form = $event->getForm();

        $values = $form->getData();

        $user = [
            'studentnummer' => $values['studentnummer'],
            'naam' => $values['naam'],
            'email' => $values['email'],
            'klas' => $values['klas'],
            'adres' => $values['adres'],
            'postcode' => $values['postcode'],
            'plaats' => $values['plaats'],
            'leeftijd' => $values['leeftijd'],
            'password' => $values['wachtwoord'],
        ];

        //Voer de functie van het aanmaken van de gebruiker uit
        $this->registrationController->createUser($user);
    }
}
