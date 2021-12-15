<?php

namespace App;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SecretManager
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function generate(string $email): string
    {
        $secret = $this->container->getParameter('app.secret_mail_salt');
        return md5($email . $secret);
    }

    public function verify(string $email, string $secret): bool
    {
        return $this->generate($email) === $secret;
    }
}
