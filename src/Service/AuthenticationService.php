<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wizaplace\SDK\Authentication\BadCredentials;

class AuthenticationService
{
    /** @var AuthenticationManagerInterface */
    private $authManager;

    public function __construct(AuthenticationManagerInterface $authManager)
    {
        $this->authManager = $authManager;
    }

    /**
     * Authenticate a user and store their API key where it's needed.
     * Normal log in should not use this method, but instead go through a Symfony firewall.
     * This method exists for special cases, like authenticating right after registering a user.
     *
     * @throws BadCredentials
     */
    public function authenticate(string $email, string $password): void
    {
        $token = new UsernamePasswordToken($email, $password, 'main', []);
        $this->authManager->authenticate($token);
    }
}
