<?php
/**
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Data;

use Wizaplace\SDK\Catalog\Option;
use Wizaplace\SDK\Catalog\OptionVariant;

final class ProductOptionSelectItem
{
    /**
     * @var Option
     */
    private $option;

    /**
     * @var OptionVariant
     */
    private $optionVariant;

    /**
     * @var string
     */
    private $url;

    /**
     * @var bool
     */
    private $isSelected;
    /**
     * @var bool
     */
    private $declinationExists;

    /**
     * @internal
     */
    public function __construct(Option $option, OptionVariant $optionVariant, string $url, bool $isSelected, bool $declinationExists)
    {
        $this->option = $option;
        $this->optionVariant = $optionVariant;
        $this->url = $url;
        $this->isSelected = $isSelected;
        $this->declinationExists = $declinationExists;
    }

    public function getOption(): Option
    {
        return $this->option;
    }

    public function getOptionVariant(): OptionVariant
    {
        return $this->optionVariant;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isSelected(): bool
    {
        return $this->isSelected;
    }

    public function declinationExists(): bool
    {
        return $this->declinationExists;
    }
}
