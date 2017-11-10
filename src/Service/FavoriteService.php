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
     */
    public function addDeclinationToUserFavorites(DeclinationId $declinationId) : void
    {
        $this->baseService->addDeclinationToUserFavorites($declinationId);

        // add ID to cache
        $this->favoritesIdsCache[(string) $declinationId] = true;
    }

    /**
     * @see \Wizaplace\SDK\Favorite\FavoriteService::removeDeclinationToUserFavorites
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
