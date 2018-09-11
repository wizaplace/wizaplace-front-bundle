<?php
/**
 * Copied from https://github.com/FriendsOfSymfony/FOSUserBundle/blob/007fd9bd48a17d23b1c38b01115db202db7d989c/Security/LoginManager.php
 * @copyright   FriendsOfSymfony <http://friendsofsymfony.github.com/>
 * @license     MIT https://github.com/FriendsOfSymfony/FOSUserBundle/blob/007fd9bd48a17d23b1c38b01115db202db7d989c/LICENSE
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

class LoginManager
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var UserCheckerInterface
     */
    private $userChecker;
    /**
     * @var SessionAuthenticationStrategyInterface
     */
    private $sessionStrategy;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var AuthenticationSuccessHandler
     */
    private $successHandler;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserCheckerInterface $userChecker,
        SessionAuthenticationStrategyInterface $sessionStrategy,
        RequestStack $requestStack,
        AuthenticationSuccessHandler $successHandler
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userChecker = $userChecker;
        $this->sessionStrategy = $sessionStrategy;
        $this->requestStack = $requestStack;
        $this->successHandler = $successHandler;
    }

    final public function logInUser(string $firewallName, UserInterface $user): void
    {
        $this->userChecker->checkPreAuth($user);

        $token = $this->createToken($firewallName, $user);
        $request = $this->requestStack->getCurrentRequest();

        if ($request !== null) {
            $this->sessionStrategy->onAuthentication($request, $token);
            $this->successHandler->onAuthenticationSuccess($request, $token);
        }

        $this->tokenStorage->setToken($token);
    }

    protected function createToken($firewall, UserInterface $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
    }

    final public function logOutUser() : void
    {
        $this->tokenStorage->setToken(null);
    }
}
