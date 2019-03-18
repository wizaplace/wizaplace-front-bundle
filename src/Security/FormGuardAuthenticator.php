<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Wizaplace\SDK\ApiClient;
use Wizaplace\SDK\Authentication\BadCredentials;
use Wizaplace\SDK\User\UserService;
use WizaplaceFrontBundle\Controller\AuthController;

class FormGuardAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var AuthenticationFailureHandlerInterface
     */
    private $authenticationFailureHandler;

    /**
     * @var AuthenticationSuccessHandlerInterface
     */
    private $authenticationSuccessHandler;

    public function __construct(
        ApiClient $apiClient,
        UserService $userService,
        CsrfTokenManagerInterface $csrfTokenManager,
        RouterInterface $router,
        AuthenticationFailureHandlerInterface $authenticationFailureHandler,
        AuthenticationSuccessHandlerInterface $authenticationSuccessHandler
    ) {
        $this->apiClient = $apiClient;
        $this->userService = $userService;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->router = $router;
        $this->authenticationFailureHandler = $authenticationFailureHandler;
        $this->authenticationSuccessHandler = $authenticationSuccessHandler;
    }

    /**
     * @inheritdoc
     */
    public function supports(Request $request)
    {
        return
            $request->isMethod('POST')
            && $request->attributes->get('_route') === 'login'
            && $request->request->has(AuthController::EMAIL_FIELD_NAME)
            && $request->request->has(AuthController::PASSWORD_FIELD_NAME)
            && $request->request->has(AuthController::CSRF_FIELD_NAME);
    }

    /**
     * @inheritdoc
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->router->generate('login_form'));
    }

    /**
     * @inheritdoc
     */
    public function getCredentials(Request $request)
    {
        $csrfToken = new CsrfToken(AuthController::CSRF_LOGIN_ID, $request->request->get(AuthController::CSRF_FIELD_NAME));

        if ($this->csrfTokenManager->isTokenValid($csrfToken) === false) {
            throw new InvalidCsrfTokenException('Invalid CSRF token.');
        }

        return [
            'email' => $request->request->get(AuthController::EMAIL_FIELD_NAME),
            'password' => $request->request->get(AuthController::PASSWORD_FIELD_NAME),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $apiKey = $this->apiClient->authenticate($credentials['email'], $credentials['password']);
        } catch (BadCredentials $e) {
            throw new BadCredentialsException($e->getMessage(), $e->getCode(), $e);
        }

        return new User($apiKey, $this->userService->getProfileFromId($apiKey->getId()), $this->userService);
    }

    /**
     * @inheritdoc
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // check is done in getUser()
        return true;
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->authenticationFailureHandler->onAuthenticationFailure($request, $exception);
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return $this->authenticationSuccessHandler->onAuthenticationSuccess($request, $token);
    }

    /**
     * @inheritdoc
     */
    public function supportsRememberMe()
    {
        return true;
    }
}
