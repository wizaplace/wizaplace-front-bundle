<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Security;

use phpseclib\Crypt\Blowfish;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Wizaplace\SDK\ApiClient;
use Wizaplace\SDK\Authentication\ApiKey;
use Wizaplace\SDK\User\UserService;

class TokenGuardAuthenticator extends AbstractGuardAuthenticator
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
     * @var Blowfish
     */
    private $cipher;

    public function __construct(ApiClient $apiClient, UserService $userService, Blowfish $cipher)
    {
        $this->apiClient = $apiClient;
        $this->userService = $userService;
        $this->cipher = $cipher;
    }

    /**
     * @inheritdoc
     */
    public function supports(Request $request)
    {
        return $request->headers->has('x-cross-authentication');
    }

    /**
     * @inheritdoc
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new Response('x-cross-authentication header required', 401);
    }

    /**
     * @inheritdoc
     */
    public function getCredentials(Request $request)
    {
        return $request->headers->get('x-cross-authentication');
    }

    /**
     * @inheritdoc
     */
    public function getUser($xCrossToken, UserProviderInterface $userProvider)
    {
        [$userId, $apiKey] = $this->decryptToken($xCrossToken);

        $apiKey = new ApiKey([
            'id' => $userId,
            'apiKey' => $apiKey,
        ]);

        $this->apiClient->setApiKey($apiKey);

        try {
            $user = $this->userService->getProfileFromId($userId);
        } catch (\Throwable $e) {
            if ($e->getCode() === 401) {
                throw new BadCredentialsException("Invalid cross token", $e->getCode(), $e);
            }

            throw new BadCredentialsException($e->getMessage(), $e->getCode(), $e);
        }

        if ($user->isVendor() === true) {
            throw new AuthenticationException('You are not allowed to login as a vendor or an admin with a x-cross-token');
        }

        return new User($apiKey, $user, $this->userService);
    }

    /**
     * @inheritdoc
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // check is done on getUser()
        return true;
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse([
            'message' => $exception->getMessage(),
        ], Response::HTTP_FORBIDDEN);
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function supportsRememberMe()
    {
        false;
    }

    /**
     * @param string $xCrossToken
     * @return array [userId, apiKey]
     * @throws AuthenticationException if token cant be decrypted
     */
    private function decryptToken(string $xCrossToken): array
    {
        /** @var string|bool $xCrossToken */
        $xCrossToken = $this->cipher->decrypt($xCrossToken);

        if (is_string($xCrossToken) === false) {
            throw new AuthenticationException("Invalid token format");
        }

        $segments = explode(':', $xCrossToken, 2);

        if (count($segments) !== 2) {
            throw new AuthenticationException("Invalid token format");
        }

        return [
            (int) $segments[0], // the userId
            $segments[1],       // the apiKey
        ];
    }
}
