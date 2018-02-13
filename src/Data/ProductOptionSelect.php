<?php
/**
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Data;

use Wizaplace\SDK\Catalog\Option;

final class ProductOptionSelect
{
    /**
     * @var Option
     */
    private $option;

    /**
     * @var ProductOptionSelectItem[]
     */
    private $items;

    /**
     * @internal
     */
    public function __construct(Option $option, ProductOptionSelectItem ...$items)
    {
        $this->option = $option;
        $this->items = $items;
    }

    public function getOption(): Option
    {
        return $this->option;
    }

    /**
     * @return ProductOptionSelectItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
