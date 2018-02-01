<?php
/**
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Service;

use Wizaplace\SDK\Catalog\CatalogService;
use Wizaplace\SDK\Catalog\Option;
use Wizaplace\SDK\Catalog\OptionVariant;
use WizaplaceFrontBundle\Data\ProductOptionSelect;
use WizaplaceFrontBundle\Data\ProductOptionSelectItem;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class DeclinationServiceTest extends BundleTestCase
{
    public function testListProductOptionSelectsFromSelectedDeclination()
    {
        $container = self::$kernel->getContainer();
        $product = $container->get(CatalogService::class)->getProductById('2');
        $declinations = $product->getDeclinations();
        $declinationService = $container->get('test.WizaplaceFrontBundle\Service\DeclinationService');
        $selects = $declinationService->listProductOptionSelectsFromSelectedDeclination($product, reset($declinations));

        self::assertCount(2, $selects);
        self::assertContainsOnly(ProductOptionSelect::class, $selects);
        foreach ($selects as $select) {
            self::assertInstanceOf(Option::class, $select->getOption());

            $items = $select->getItems();
            self::assertNotEmpty($items);
            self::assertContainsOnly(ProductOptionSelectItem::class, $items);
            foreach ($items as $item) {
                self::assertInstanceOf(Option::class, $item->getOption());
                self::assertInstanceOf(OptionVariant::class, $item->getOptionVariant());
                self::assertInternalType('string', $item->getUrl());
                self::assertInternalType('bool', $item->isSelected());
                self::assertInternalType('bool', $item->declinationExists());
            }
        }
    }

    public function testListProductOptionSelectsFromSelectedVariantsIds()
    {
        $container = self::$kernel->getContainer();
        $product = $container->get(CatalogService::class)->getProductById('2');
        $declinationService = $container->get('test.WizaplaceFrontBundle\Service\DeclinationService');
        $selects = $declinationService->listProductOptionSelectsFromSelectedVariantsIds($product, [1, 5]);

        self::assertCount(2, $selects);
        self::assertContainsOnly(ProductOptionSelect::class, $selects);
        foreach ($selects as $select) {
            self::assertInstanceOf(Option::class, $select->getOption());

            $items = $select->getItems();
            self::assertNotEmpty($items);
            self::assertContainsOnly(ProductOptionSelectItem::class, $items);
            foreach ($items as $item) {
                self::assertInstanceOf(Option::class, $item->getOption());
                self::assertInstanceOf(OptionVariant::class, $item->getOptionVariant());
                self::assertInternalType('string', $item->getUrl());
                self::assertInternalType('bool', $item->isSelected());
                self::assertInternalType('bool', $item->declinationExists());
            }
        }
    }
}
