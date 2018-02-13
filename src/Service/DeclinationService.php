<?php
/**
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Wizaplace\SDK\Catalog\Declination;
use Wizaplace\SDK\Catalog\Option;
use Wizaplace\SDK\Catalog\OptionVariant;
use Wizaplace\SDK\Catalog\Product;
use Wizaplace\SDK\Exception\NotFound;
use WizaplaceFrontBundle\Data\ProductOptionSelect;
use WizaplaceFrontBundle\Data\ProductOptionSelectItem;

class DeclinationService
{
    /**
     * @var ProductUrlGenerator
     */
    private $productUrlGenerator;

    public function __construct(ProductUrlGenerator $productUrlGenerator)
    {
        $this->productUrlGenerator = $productUrlGenerator;
    }

    /**
     * Takes a product and one of its declination, and gives you all the data you need to build the option selectors.
     *
     * @param Product $product
     * @param Declination $currentDeclination
     * @return ProductOptionSelect[]
     */
    public function listProductOptionSelectsFromSelectedDeclination(Product $product, Declination $currentDeclination): array
    {
        $variantIdByOptionId = [];
        foreach ($currentDeclination->getOptions() as $option) {
            $variantIdByOptionId[$option->getId()] = $option->getVariantId();
        }

        return $this->listProductOptionSelects($product, $variantIdByOptionId);
    }

    /**
     * Takes a product and a list of options' variants' ids, and gives you all the data you need to build the option selectors.
     *
     * @param Product $product
     * @param int[] $selectedVariantsIds
     * @return ProductOptionSelect[]
     */
    public function listProductOptionSelectsFromSelectedVariantsIds(Product $product, array $selectedVariantsIds): array
    {
        $variantIdByOptionId = [];
        foreach ($product->getOptions() as $option) {
            $intersection = array_intersect($selectedVariantsIds, array_map(static function (OptionVariant $variant): int {
                return $variant->getId();
            }, $option->getVariants()));

            if (count($intersection) !== 1) {
                throw new \Exception('each option should have exactly 1 variant selected');
            }

            $variantIdByOptionId[$option->getId()] = reset($intersection);
        }

        return $this->listProductOptionSelects($product, $variantIdByOptionId);
    }

    /**
     * @return ProductOptionSelect[]
     */
    private function listProductOptionSelects(Product $product, array $variantIdByOptionId): array
    {
        return array_map(function (Option $option) use ($product, $variantIdByOptionId) : ProductOptionSelect {
            $items = array_map(function (OptionVariant $variant) use ($product, $option, $variantIdByOptionId) : ProductOptionSelectItem {
                $isSelected = false;
                if (isset($variantIdByOptionId[$option->getId()])) {
                    $isSelected = $variantIdByOptionId[$option->getId()] === $variant->getId();
                }
                $variantIdByOptionId[$option->getId()] = $variant->getId();
                try {
                    $declinationId = $product->getDeclinationFromOptions($variantIdByOptionId)->getId();
                    $url = $this->productUrlGenerator->generateUrlFromProduct($product, $declinationId);
                    $declinationExists = true;
                } catch (NotFound $e) {
                    $url = $this->productUrlGenerator->generateUrlFromProductWithOptions($product, $variantIdByOptionId);
                    $declinationExists = false;
                }

                return new ProductOptionSelectItem(
                    $option,
                    $variant,
                    $url,
                    $isSelected,
                    $declinationExists
                );
            }, $option->getVariants());

            return new ProductOptionSelect($option, ...$items);
        }, $product->getOptions());
    }
}
