<?php

namespace App\Controller;

use Bolt\Entity\Content;
use Bolt\Factory\ContentFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserFormController extends AbstractController
{
    /** @var Request $request  */
    private Request $request;

    /** @var ContentFactory $factory */
    private ContentFactory $factory;

    public function __construct(RequestStack $requestStack, ContentFactory $factory)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->factory = $factory;
    }


    /**
     * @Route("/submitForm", name="submit_form", methods={"POST"})
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

        $this->checkForm($values);

        // Loop over de values en 'upsert' (update or insert) deze in de database
        $this->upsertForm($values);

        return $this->redirectToRoute('page/thanks');
    }

    public function checkForm(array $data) : bool
    {
        $naam = $data['naam'];
        $studentnummer = $data['studentnummer'];
        $kilometer = $data['kilometer'];
        $minuten = $data['minuten'];
        $vervoer = $data['vervoer'];
        $startles = $data['startles'];
        $eindles = $data['eindles'];
        $extra = $data['extra'];

        // Check of 1 van de velden leeg is
        if (empty($studentnummer || $naam || $kilometer || $minuten || $vervoer || $startles || $eindles)) {
            throw new \Exception("Er is een veld niet ingevuld 🤔");
        }

        return true;
    }

    // Upsert (Update or insert) de data in de database
    public function upsertForm(array $form): Content
    {
        // Check of er een record bestaat anders creeer er 1
        $record = $this->factory->upsert('antwoorden', [
            'studentnummer' => $form['studentnummer']
        ]);

        $values = [
            'studentnummer' => $form['studentnummer'],
            'naam' => $form['naam'],
            'kilometer' => $form['kilometer'],
            'minuten' => $form['minuten'],
            'vervoer' => $form['vervoer'],
            'startles' => $form['startles'],
            'eindles' => $form['eindles'],
            'extra' => $form['extra'] ?? ''
        ];

        //Voor elke waarde vul deze in de database
        foreach ($values as $name => $value) {
            $record->setFieldValue($name, $value);
        }

        $record->setAuthor($this->getUser());

        $this->factory->save($record);

        return $record;
    }

}
