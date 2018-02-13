<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wizaplace\SDK\Catalog\DeclinationId;
use Wizaplace\SDK\Catalog\DeclinationSummary;
use Wizaplace\SDK\Catalog\Product;
use Wizaplace\SDK\Catalog\ProductCategory;
use Wizaplace\SDK\Catalog\ProductSummary;
use Wizaplace\SDK\Catalog\SearchCategoryPath;

class ProductUrlGenerator
{
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Product|ProductSummary|DeclinationSummary $product
     */
    public function generateProductUrl($product, ?DeclinationId $declinationId = null): string
    {
        if ($product instanceof Product) {
            return $this->generateUrlFromProduct($product, $declinationId);
        }
        if ($product instanceof ProductSummary) {
            return $this->generateUrlFromProductSummary($product, $declinationId);
        }
        if ($product instanceof DeclinationSummary) {
            return $this->generateUrlFromProductDeclinationSummary($product);
        }

        throw new \InvalidArgumentException('Cannot generate an url from given $product');
    }

    public function generateUrlFromProduct(Product $product, ?DeclinationId $declinationId = null): string
    {
        return $this->generateUrl(
            $product->getSlug(),
            array_map(static function (ProductCategory $category) : string {
                return $category->getSlug();
            }, $product->getCategoryPath()),
            $declinationId
        );
    }

    public function generateUrlFromProductWithOptions(Product $product, array $optionVariantIds): string
    {
        return $this->generateUrl(
            $product->getSlug(),
            array_map(static function (ProductCategory $category) : string {
                return $category->getSlug();
            }, $product->getCategoryPath()),
            null,
            $optionVariantIds
        );
    }

    public function generateUrlFromProductSummary(ProductSummary $productSummary, ?DeclinationId $declinationId = null): string
    {
        return $this->generateUrl(
            $productSummary->getSlug(),
            array_map(static function (SearchCategoryPath $category) : string {
                return $category->getSlug();
            }, $productSummary->getCategoryPath()),
            $declinationId
        );
    }

    public function generateUrlFromProductDeclinationSummary(DeclinationSummary $declinationSummary): string
    {
        return $this->generateUrl(
            $declinationSummary->getSlug(),
            array_map(static function (ProductCategory $category) : string {
                return $category->getSlug();
            }, $declinationSummary->getCategoryPath()),
            $declinationSummary->getId()
        );
    }

    /**
     * @param string[] $categoryPath
     */
    private function generateUrl(string $productSlug, array $categoryPath, ?DeclinationId $declinationId = null, array $optionVariantIds = []): string
    {
        $params = [
            'categoryPath' => join('/', $categoryPath),
            'slug' => $productSlug,
        ];
        if ($declinationId !== null) {
            $params['d'] = (string) $declinationId;
        } elseif (!empty($optionVariantIds)) {
            $optionVariantIds = array_values($optionVariantIds);
            sort($optionVariantIds);
            $params['options'] = $optionVariantIds;
        }

        return $this->urlGenerator->generate('product', $params);
    }
}
