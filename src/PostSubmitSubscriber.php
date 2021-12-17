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

        $this->registrationController->createUser($user);

        $this->registrationController->upsertUser($data);
    }
}
