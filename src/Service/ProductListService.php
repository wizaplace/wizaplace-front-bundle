<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Wizaplace\SDK\Catalog\CatalogService;
use Wizaplace\SDK\Catalog\ProductSummary;

class ProductListService
{
    /** @var CatalogService */
    private $productService;

    public function __construct(CatalogService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @return ProductSummary[]
     */
    public function getLatestProducts(int $maxProductCount = 6) : array
    {
        if ($maxProductCount === 0) {
            return [];
        }

        return $this->productService->search('', [], ['createdAt' => 'desc'], $maxProductCount)->getProducts();
    }

    /**
     * @return ProductSummary[]
     */
    public function getProductsWithAttributes(array $attributesIds, int $maxProductCount = 6): array
    {
        if ($maxProductCount === 0) {
            return [];
        }

        $filters = [];
        foreach ($attributesIds as $attributesId) {
            $filters[$attributesId] = 'Y';
        }

        return $this->productService->search('', $filters, ['createdAt' => 'desc'], $maxProductCount)->getProducts();
    }
}
