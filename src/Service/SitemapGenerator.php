<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Dpn\XmlSitemapBundle\Sitemap\Entry;
use Dpn\XmlSitemapBundle\Sitemap\GeneratorInterface;
use SitemapGenerator\Entity\Url;
use SitemapGenerator\Provider\ProviderInterface;
use SitemapGenerator\Sitemap\Sitemap;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Wizaplace\SDK\Seo\SeoService;
use Wizaplace\SDK\Seo\SlugCatalogItem;
use Wizaplace\SDK\Seo\SlugTargetType;

class SitemapGenerator implements ProviderInterface
{
    public const SITEMAP_ROUTE_OPTION_NAME = 'sitemap';

    /** @var SeoService */
    private $seoService;

    /** @var RouterInterface */
    private $router;

    public function __construct(SeoService $seoService, RouterInterface $router)
    {
        $this->seoService = $seoService;
        $this->router = $router;
    }

    /**
     * Populate a sitemap.
     *
     * @param Sitemap $sitemap The current sitemap.
     */
    public function populate(Sitemap $sitemap): void
    {
        $this->populateStaticEntries($sitemap);
        $this->populateDynamicEntries($sitemap);
    }

    /**
     * Takes all routes with a 'sitemap' option set to true, and generate them without any params.
     *
     * @param Sitemap $sitemap
     */
    private function populateStaticEntries(Sitemap $sitemap): void
    {
        $routeCollection = $this->router->getRouteCollection();
        foreach ($routeCollection as $routeName => $route) {
            if ($route->getOption(self::SITEMAP_ROUTE_OPTION_NAME)) {
                $url = new Url();
                $url->setLoc($this->router->generate($routeName, [], UrlGeneratorInterface::ABSOLUTE_PATH));
                $sitemap->add($url);
            }
        }
    }

    /**
     * Takes known dynamic routes, and generates them with params from the slug catalog
     *
     * @param Sitemap $sitemap
     * @throws \Wizaplace\SDK\Exception\JsonDecodingError
     */
    private function populateDynamicEntries(Sitemap $sitemap): void
    {
        $cmsPageRoute = 'cms_page';
        $cmsPageRouteExists = $this->router->getRouteCollection()->get($cmsPageRoute) !== null;

        $categoryRoute = 'category';
        $categoryRouteExists = $this->router->getRouteCollection()->get($categoryRoute) !== null;

        $attrVariantRoute = 'attribute_variant';
        $attrVariantRouteExists = $this->router->getRouteCollection()->get($attrVariantRoute) !== null;

        $companyRoute = 'company';
        $companyRouteExists = $this->router->getRouteCollection()->get($companyRoute) !== null;

        $productRoute = 'product';
        $productRouteExists = $this->router->getRouteCollection()->get($productRoute) !== null;

        if (!$cmsPageRouteExists && !$categoryRouteExists && !$attrVariantRouteExists && !$companyRouteExists && !$productRouteExists) {
            return;
        }

        $slugsCatalog = $this->seoService->listSlugs();
        foreach ($slugsCatalog as $slugCatalogItem) {
            $type = $slugCatalogItem->getTarget()->getObjectType();

            $url = new Url();

            if ($cmsPageRouteExists && $type->equals(SlugTargetType::CMS_PAGE())) {
                $url->setLoc($this->router->generate($cmsPageRoute, ['slug' => $slugCatalogItem->getSlug()], UrlGeneratorInterface::ABSOLUTE_PATH));
            } elseif ($categoryRouteExists && $type->equals(SlugTargetType::CATEGORY())) {
                $url->setLoc($this->router->generate($categoryRoute, ['slug' => $slugCatalogItem->getSlug()], UrlGeneratorInterface::ABSOLUTE_PATH));
            } elseif ($attrVariantRouteExists && $type->equals(SlugTargetType::ATTRIBUTE_VARIANT())) {
                $url->setLoc($this->router->generate($attrVariantRoute, ['slug' => $slugCatalogItem->getSlug()], UrlGeneratorInterface::ABSOLUTE_PATH));
            } elseif ($companyRouteExists && $type->equals(SlugTargetType::COMPANY())) {
                $url->setLoc($this->router->generate($companyRoute, ['slug' => $slugCatalogItem->getSlug()], UrlGeneratorInterface::ABSOLUTE_PATH));
            } elseif ($productRouteExists && $type->equals(SlugTargetType::PRODUCT())) {
                $url->setLoc($this->router->generate($productRoute, [
                    'slug' => $slugCatalogItem->getSlug(),
                    'categoryPath' => join(
                        '/',
                        array_map(function (SlugCatalogItem $data): string {
                            return $data->getSlug();
                        }, $slugCatalogItem->getCategoryPath())
                    ),
                ], UrlGeneratorInterface::ABSOLUTE_PATH));
            } else {
                continue;
            }

            $sitemap->add($url);
        }
    }
}
