<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Wizaplace\SDK\Catalog\AbstractCatalogServiceDecorator;

class CategoryTreeCacheDecorator extends AbstractCatalogServiceDecorator
{
    private $categoryTreeCache;

    /**
     * @inheritdoc
     */
    public function getCategoryTree(): array
    {
        if (!isset($this->categoryTreeCache)) {
            $this->categoryTreeCache = parent::getCategoryTree();
        }

        return $this->categoryTreeCache;
    }

    public function clearCategoryTreeCache(): void
    {
        unset($this->categoryTreeCache);
    }
}
