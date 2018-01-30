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
     * Fetches at most $maxProductCount products created the most recently.
     *
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
     * Fetches at most $maxProductCount products created the most recently with the attribute $attributeId being checked.
     *
     * @param int $attributeId the ID of a \Wizaplace\SDK\Catalog\AttributeType::CHECKBOX_UNIQUE attribute
     * @return ProductSummary[]
     */
    public function getLatestProductsWithAttributeChecked(int $attributeId, int $maxProductCount = 6): array
    {
        if ($maxProductCount === 0) {
            return [];
        }

        return $this->productService->search('', [$attributeId => 'Y'], ['createdAt' => 'desc'], $maxProductCount)->getProducts();
    }
}
