<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Service;

use GuzzleHttp\Psr7\Uri;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Wizaplace\SDK\User\UserService;
use WizaplaceFrontBundle\Entity\InitiateResetPasswordCommand;
use WizaplaceFrontBundle\Form\InitiateResetPasswordType;

class AuthenticationService
{
    /** @var AuthenticationManagerInterface */
    private $authManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var UserService */
    private $userService;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(
        AuthenticationManagerInterface $authManager,
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        UserService $userService,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->authManager = $authManager;
        $this->tokenStorage = $tokenStorage;
        $this->formFactory = $formFactory;
        $this->userService = $userService;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Authenticate a user and store their API key where it's needed.
     * Normal log in should not use this method, but instead go through a Symfony firewall.
     * This method exists for special cases, like authenticating right after registering a user.
     *
     * @throws AuthenticationException
     */
    public function authenticate(string $email, string $password): void
    {
        $token = new UsernamePasswordToken($email, $password, 'main', []);
        $authToken = $this->authManager->authenticate($token);
        $this->tokenStorage->setToken($authToken);
    }

    /**
     * @param InitiateResetPasswordCommand $command
     * @param string $resetPasswordFormRoute The route which will be linked from the password recovery email.
     * @param string $tokenParamKey The key of the route param which will receive the token.
     * @throws \Exception
     */
    public function initiateResetPassword(InitiateResetPasswordCommand $command, string $resetPasswordFormRoute = 'reset_password_form', string $tokenParamKey = 'token'): void
    {
        try {
            $recoveryUrl = $this->urlGenerator->generate(
                $resetPasswordFormRoute,
                [$tokenParamKey => 'token_placeholder'],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $recoveryUrl = new Uri(str_replace('token_placeholder', '', $recoveryUrl));

            $this->userService->recoverPassword($command->getEmail(), $recoveryUrl);
        } catch (\Throwable $e) {
            throw new \Exception('failed to initiate password reset', 500, $e);
        }
    }

    public function getInitiateResetPasswordForm(): FormInterface
    {
        // the empty name is for backward compatibility
        return $this->formFactory->createNamed('', InitiateResetPasswordType::class);
    }
}
