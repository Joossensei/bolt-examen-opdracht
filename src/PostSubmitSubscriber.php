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

        $data = $form->getData();

        $user = [
            'email' => $data["email"],
            'naam' => $data["naam"],
            'password' => $data["wachtwoord"],
            'studentnummer' => $data["studentnummer"]
        ];

        //Voer de functie van het aanmaken van de gebruiker uit
        $this->registrationController->createUser($user);
    }
}
