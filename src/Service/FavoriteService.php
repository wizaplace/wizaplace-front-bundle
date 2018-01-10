<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Wizaplace\SDK\Catalog\DeclinationId;
use Wizaplace\SDK\Catalog\DeclinationSummary;

/**
 * Decorates {@see \Wizaplace\SDK\Favorite\FavoriteService}.
 * Adds a request-scoped cache.
 */
class FavoriteService implements LogoutHandlerInterface
{
    /** @var \Wizaplace\SDK\Favorite\FavoriteService */
    private $baseService;

    /** @var null|array */
    private $favoritesIdsCache;

    public function __construct(\Wizaplace\SDK\Favorite\FavoriteService $baseService)
    {
        $this->baseService = $baseService;
    }

    /**
     * @see \Wizaplace\SDK\Favorite\FavoriteService::getAll
     * @return DeclinationSummary[]
     * @throws \Wizaplace\SDK\Authentication\AuthenticationRequired
     */
    public function getAll() : array
    {
        $result = $this->baseService->getAll();

        // re-build cache entirely
        $this->favoritesIdsCache = array_reverse(array_map(function (DeclinationSummary $declination): string {
            return (string) $declination->getId();
        }, $result));

        return $result;
    }

    /**
     * @see \Wizaplace\SDK\Favorite\FavoriteService::isInFavorites
     * @throws \Wizaplace\SDK\Authentication\AuthenticationRequired
     */
    public function isInFavorites(DeclinationId $declinationId) : bool
    {
        if (!is_array($this->favoritesIdsCache)) {
            $this->getAll();
        }

        return isset($this->favoritesIdsCache[(string) $declinationId]);
    }

    /**
     * @see \Wizaplace\SDK\Favorite\FavoriteService::addDeclinationToUserFavorites
     * @throws \Wizaplace\SDK\Authentication\AuthenticationRequired
     * @throws \Wizaplace\SDK\Favorite\Exception\CannotFavoriteDisabledOrInexistentDeclination
     * @throws \Wizaplace\SDK\Favorite\Exception\FavoriteAlreadyExist
     */
    public function addDeclinationToUserFavorites(DeclinationId $declinationId) : void
    {
        $this->baseService->addDeclinationToUserFavorites($declinationId);

        // add ID to cache
        $this->favoritesIdsCache[(string) $declinationId] = true;
    }

    /**
     * @see \Wizaplace\SDK\Favorite\FavoriteService::removeDeclinationToUserFavorites
     * @throws \Wizaplace\SDK\Authentication\AuthenticationRequired
     */
    public function removeDeclinationToUserFavorites(DeclinationId $declinationId) : void
    {
        $this->baseService->removeDeclinationToUserFavorites($declinationId);

        // remove ID from cache
        unset($this->favoritesIdsCache[(string) $declinationId]);
    }

    /**
     * @inheritdoc
     */
    public function logout(Request $request, Response $response, TokenInterface $token): void
    {
        $this->favoritesIdsCache = null;
    }
}
