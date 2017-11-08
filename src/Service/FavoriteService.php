<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Wizaplace\SDK\Catalog\DeclinationSummary;

class FavoriteService
{
    /** @var \Wizaplace\SDK\Favorite\FavoriteService */
    private $baseService;

    /** @var SessionInterface */
    private $session;

    private const FAVORITES_IDS_CACHE_SESSION_KEY = self::class.'::FAVORITES_IDS_CACHE_SESSION_KEY';

    public function __construct(\Wizaplace\SDK\Favorite\FavoriteService $baseService, SessionInterface $session)
    {
        $this->baseService = $baseService;
        $this->session = $session;
    }

    /**
     * @see \Wizaplace\SDK\Favorite\FavoriteService::getAll
     * @return DeclinationSummary[]
     */
    public function getAll() : array
    {
        $result = $this->baseService->getAll();

        // re-build cache entirely
        $cache = array_reverse(array_map(function (DeclinationSummary $declination): string {
            return $declination->getId();
        }, $result));
        $this->session->set(self::FAVORITES_IDS_CACHE_SESSION_KEY, $cache);

        return $result;
    }

    /**
     * @see \Wizaplace\SDK\Favorite\FavoriteService::isInFavorites
     */
    public function isInFavorites(string $declinationId) : bool
    {
        $cache = $this->session->get(self::FAVORITES_IDS_CACHE_SESSION_KEY, null);
        if (!is_array($cache)) {
            $this->getAll();
        }
        $cache = $this->session->get(self::FAVORITES_IDS_CACHE_SESSION_KEY, []);

        return isset($cache[$declinationId]);
    }

    /**
     * @see \Wizaplace\SDK\Favorite\FavoriteService::addDeclinationToUserFavorites
     */
    public function addDeclinationToUserFavorites(string $declinationId) : void
    {
        $this->baseService->addDeclinationToUserFavorites($declinationId);

        // add ID to cache
        $cache = $this->session->get(self::FAVORITES_IDS_CACHE_SESSION_KEY, null);
        $cache[$declinationId] = true;
        $this->session->set(self::FAVORITES_IDS_CACHE_SESSION_KEY, $cache);
    }

    /**
     * @see \Wizaplace\SDK\Favorite\FavoriteService::removeDeclinationToUserFavorites
     */
    public function removeDeclinationToUserFavorites(string $declinationId) : void
    {
        $this->baseService->removeDeclinationToUserFavorites($declinationId);

        // remove ID from cache
        $cache = $this->session->get(self::FAVORITES_IDS_CACHE_SESSION_KEY, null);
        unset($cache[$declinationId]);
        $this->session->set(self::FAVORITES_IDS_CACHE_SESSION_KEY, $cache);
    }
}
