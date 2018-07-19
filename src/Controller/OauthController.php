<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\ApiClient;
use Wizaplace\SDK\User\UserService;
use WizaplaceFrontBundle\Security\LoginManager;
use WizaplaceFrontBundle\Security\User;

class OauthController extends Controller
{
    /**
     * @var LoginManager
     */
    private $loginManager;
    /**
     * @var ApiClient
     */
    private $apiClient;
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(LoginManager $loginManager, ApiClient $apiClient, UserService $userService)
    {
        $this->loginManager = $loginManager;
        $this->apiClient = $apiClient;
        $this->userService = $userService;
    }

    public function loginAction(Request $request): Response
    {
        if ($request->query->has('code')) {

            $apiKey = $this->apiClient->oauthAuthenticate($request->query->get('code'));

            $this->loginManager->logInUser(
                'main',
                new User($apiKey, $this->userService->getProfileFromId($apiKey->getId()))
            );
        }

        return new RedirectResponse($this->generateUrl('home'));
    }

    public function authorizeAction(): RedirectResponse
    {
        return new RedirectResponse($this->apiClient->getOAuthAuthorizationUrl());
    }
}
