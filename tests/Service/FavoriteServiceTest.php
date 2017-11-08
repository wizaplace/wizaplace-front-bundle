<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Service;

use Wizaplace\SDK\ApiClient;
use WizaplaceFrontBundle\Service\FavoriteService;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class FavoriteServiceTest extends BundleTestCase
{
    public function testIsInFavorites()
    {
        $container = self::$kernel->getContainer();
        $container->get(ApiClient::class)->authenticate('user@wizaplace.com', 'password');

        self::assertFalse($container->get(FavoriteService::class)->isInFavorites('1_0'));

        $container->get(FavoriteService::class)->addDeclinationToUserFavorites('1_0');

        self::assertTrue($container->get(FavoriteService::class)->isInFavorites('1_0'));

        $container->get(FavoriteService::class)->removeDeclinationToUserFavorites('1_0');

        self::assertFalse($container->get(FavoriteService::class)->isInFavorites('1_0'));
    }
}
