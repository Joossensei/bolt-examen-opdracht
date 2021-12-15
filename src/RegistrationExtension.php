<?php

namespace App;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RegistrationExtension extends AbstractExtension
{
    private SecretManager $secretManager;

    public function __construct(SecretManager $secretManager)
    {
        $this->secretManager = $secretManager;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('generate_secret', [$this, 'generateSecret'])
        ];
    }

    public function generateSecret(string $email): string
    {
        return $this->secretManager->generate($email);
    }
}
