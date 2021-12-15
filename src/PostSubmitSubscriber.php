<?php


namespace App;


use Bolt\BoltForms\Event\PostSubmitEvent;
use Bolt\BoltForms\EventSubscriber\Redirect;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;


class PostSubmitSubscriber implements EventSubscriberInterface
{
    private Request $request;

    private RedirectResponse $redirectResponse;

    public function __construct(Request $request, Redirect $redirect)
    {
        $this->request = $request;
        $this->redirectResponse = $redirect;
    }

    public static function getSubscribedEvents()
    {
        return [
            PostSubmitEvent::NAME => ['redirectSignup']
        ];
    }

    public function redirectSignup()
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
    }
}
