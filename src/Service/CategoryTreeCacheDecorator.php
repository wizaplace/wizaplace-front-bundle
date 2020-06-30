<?php

/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */

declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Wizaplace\SDK\Catalog\AbstractCatalogServiceDecorator;
use Wizaplace\SDK\Catalog\CategorySortCriteria;
use Wizaplace\SDK\SortDirection;

class CategoryTreeCacheDecorator extends AbstractCatalogServiceDecorator
{
    private $categoryTreeCache;

    /**
     * @inheritdoc
     */
    public function getCategoryTree(string $criteria = CategorySortCriteria::POSITION, string $direction = SortDirection::ASC): array
    {
        if (!isset($this->categoryTreeCache)) {
            $this->categoryTreeCache = parent::getCategoryTree($criteria, $direction);
        }

        return $this->categoryTreeCache;
    }

    public function clearCategoryTreeCache(): void
    {
        unset($this->categoryTreeCache);
    }
}
