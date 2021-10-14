<?php

/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */

declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Dpn\XmlSitemapBundle\Sitemap\Entry;
use Dpn\XmlSitemapBundle\Sitemap\GeneratorInterface;
use SitemapGenerator\Entity\RichUrl;
use SitemapGenerator\Provider\ProviderInterface;
use SitemapGenerator\Sitemap\Sitemap;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Wizaplace\SDK\Seo\SeoService;
use Wizaplace\SDK\Seo\SlugCatalogItem;
use Wizaplace\SDK\Seo\SlugTargetType;
use Symfony\Component\Routing\Route;

class SitemapGenerator implements ProviderInterface
{
    public const SITEMAP_ROUTE_OPTION_NAME = 'sitemap';

    /** @var SeoService */
    private $seoService;

    /** @var RouterInterface */
    private $router;

    /** @var string */
    private $defaultLocale;

    /** @var string[] */
    private $locales;

    public function __construct(SeoService $seoService, RouterInterface $router, string $defaultLocale, array $locales)
    {
        $this->seoService = $seoService;
        $this->router = $router;
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
    }

    public function isMultiLingual(): bool
    {
        return \count($this->locales) > 1;
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

    private function buildUrl(Sitemap $sitemap, Route $route, string $routeName, array $parameters = []): void
    {
        if ($this->isMultiLingual()) {
            $locale = $this->locales[0];
            $url = new RichUrl();

            if ($route->hasRequirement('_locale')) {
                $parameters['_locale'] = $locale;
            }

            $url->setLoc($this->router->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));

            foreach ($this->locales as $locale) {
                if ($route->hasRequirement('_locale')) {
                    $parameters['_locale'] = $locale;
                }

                $url->addAlternateUrl($locale, $this->router->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));
            }

            $sitemap->add($url);
        } else {
            $url = new RichUrl();
            $url->setLoc($this->router->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));
            $sitemap->add($url);
        }
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
            // Do not include non-GET routes in the sitemap
            if (!\in_array('GET', $route->getMethods())) {
                continue;
            }

            if ($route->getOption(self::SITEMAP_ROUTE_OPTION_NAME)) {
                $this->buildUrl($sitemap, $route, $routeName);
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
        $cmsPageRouteName = 'cms_page';
        $cmsPageRoute = $this->router->getRouteCollection()->get($cmsPageRouteName);

        $categoryRouteName = 'category';
        $categoryRoute = $this->router->getRouteCollection()->get($categoryRouteName);

        $attrVariantRouteName = 'attribute_variant';
        $attrVariantRoute = $this->router->getRouteCollection()->get($attrVariantRouteName);

        $companyRouteName = 'company';
        $companyRoute = $this->router->getRouteCollection()->get($companyRouteName);

        $productRouteName = 'product';
        $productRoute = $this->router->getRouteCollection()->get($productRouteName);

        if (\is_null($cmsPageRoute) && \is_null($categoryRoute) && \is_null($attrVariantRoute) && \is_null($companyRoute) && \is_null($productRoute)) {
            return;
        }

        $slugsCatalog = $this->seoService->listSlugs();

        //Get all pages
        if ($slugsCatalog['total'] > $slugsCatalog['limit']) {
            $pagesNumber = (int) ($slugsCatalog['total'] / $slugsCatalog['limit']);
            if ($slugsCatalog['total'] % $slugsCatalog['limit'] > 0) {
                $pagesNumber++;
            }
            for ($i = 1; $i < $pagesNumber; $i++) {
                $offset = $i * $slugsCatalog['limit'];
                foreach ($this->seoService->listSlugs($offset, (int) $slugsCatalog['limit'])['items'] as $item) {
                    $slugsCatalog['items'][] = $item;
                }
            }
            $slugsCatalog['total'] = \count($slugsCatalog['items']);
            $slugsCatalog['limit'] = null;
        }

        foreach ($slugsCatalog['items'] as $slugCatalogItem) {
            $type = $slugCatalogItem->getTarget()->getObjectType();
            $parameters = [
                'slug' => $slugCatalogItem->getSlug(),
            ];

            if ($cmsPageRoute && $type->equals(SlugTargetType::CMS_PAGE())) {
                $this->buildUrl($sitemap, $cmsPageRoute, $cmsPageRouteName, $parameters);
            } elseif ($categoryRoute && $type->equals(SlugTargetType::CATEGORY())) {
                $this->buildUrl($sitemap, $categoryRoute, $categoryRouteName, $parameters);
            } elseif ($attrVariantRoute && $type->equals(SlugTargetType::ATTRIBUTE_VARIANT())) {
                $this->buildUrl($sitemap, $attrVariantRoute, $attrVariantRouteName, $parameters);
            } elseif ($companyRoute && $type->equals(SlugTargetType::COMPANY())) {
                $this->buildUrl($sitemap, $companyRoute, $companyRouteName, $parameters);
            } elseif ($productRoute && $type->equals(SlugTargetType::PRODUCT())) {
                $parameters['categoryPath'] = join(
                    '/',
                    array_map(
                        function (SlugCatalogItem $data): string {
                            return $data->getSlug();
                        },
                        $slugCatalogItem->getCategoryPath()
                    )
                );

                $this->buildUrl($sitemap, $productRoute, $productRouteName, $parameters);
            } else {
                continue;
            }
        }
    }
}
