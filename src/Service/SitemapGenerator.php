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
    private $alternateLocales;

    public function __construct(SeoService $seoService, RouterInterface $router, string $defaultLocale, array $locales)
    {
        $this->seoService = $seoService;
        $this->router = $router;
        $this->defaultLocale = $defaultLocale;

        // On supprime la locale par défaut des langues alternatives
        if (false !== $key = array_search($defaultLocale, $locales)) {
            unset($locales[$key]);
        }

        $this->alternateLocales = $locales;
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
            // Do include non-GET routes in the sitemap
            if (!in_array('GET', $route->getMethods())) {
                continue;
            }

            if ($route->getOption(self::SITEMAP_ROUTE_OPTION_NAME)) {
                $url = new RichUrl();
                $parameters = [];

                if ($route->hasRequirement('_locale')) {
                    $parameters['_locale'] = $this->defaultLocale;
                }

                $url->setLoc($this->router->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));

                foreach ($this->alternateLocales as $locale) {
                    $url->addAlternateUrl($locale, $this->router->generate($routeName, ['_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL));
                }

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

        if (is_null($cmsPageRoute) && is_null($categoryRoute) && is_null($attrVariantRoute) && is_null($companyRoute) && is_null($productRoute)) {
            return;
        }

        $slugsCatalog = $this->seoService->listSlugs();
        foreach ($slugsCatalog as $slugCatalogItem) {
            $type = $slugCatalogItem->getTarget()->getObjectType();

            $url = new RichUrl();

            $parameters = [
                'slug' => $slugCatalogItem->getSlug(),
            ];

            if ($cmsPageRoute && $type->equals(SlugTargetType::CMS_PAGE())) {
                if ($cmsPageRoute->hasRequirement('_locale')) {
                    $parameters['_locale'] = $this->defaultLocale;
                }

                $url->setLoc($this->router->generate($cmsPageRouteName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));

                foreach ($this->alternateLocales as $locale) {
                    $parameters['_locale'] = $locale;
                    $url->addAlternateUrl($locale, $this->router->generate($cmsPageRouteName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));
                }
            } elseif ($categoryRoute && $type->equals(SlugTargetType::CATEGORY())) {
                if ($categoryRoute->hasRequirement('_locale')) {
                    $parameters['_locale'] = $this->defaultLocale;
                }

                $url->setLoc($this->router->generate($categoryRouteName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));

                foreach ($this->alternateLocales as $locale) {
                    $parameters['_locale'] = $locale;
                    $url->addAlternateUrl($locale, $this->router->generate($categoryRouteName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));
                }
            } elseif ($attrVariantRoute && $type->equals(SlugTargetType::ATTRIBUTE_VARIANT())) {
                if ($attrVariantRoute->hasRequirement('_locale')) {
                    $parameters['_locale'] = $this->defaultLocale;
                }

                $url->setLoc($this->router->generate($attrVariantRouteName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));

                foreach ($this->alternateLocales as $locale) {
                    $parameters['_locale'] = $locale;
                    $url->addAlternateUrl($locale, $this->router->generate($attrVariantRouteName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));
                }
            } elseif ($companyRoute && $type->equals(SlugTargetType::COMPANY())) {
                if ($companyRoute->hasRequirement('_locale')) {
                    $parameters['_locale'] = $this->defaultLocale;
                }

                $url->setLoc($this->router->generate($companyRouteName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));

                foreach ($this->alternateLocales as $locale) {
                    $parameters['_locale'] = $locale;
                    $url->addAlternateUrl($locale, $this->router->generate($companyRouteName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));
                }
            } elseif ($productRoute && $type->equals(SlugTargetType::PRODUCT())) {
                if ($productRoute->hasRequirement('_locale')) {
                    $parameters['_locale'] = $this->defaultLocale;
                }

                $parameters['categoryPath'] = join(
                    '/',
                    array_map(function (SlugCatalogItem $data): string {
                        return $data->getSlug();
                    }, $slugCatalogItem->getCategoryPath())
                );

                $url->setLoc($this->router->generate($productRouteName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));

                foreach ($this->alternateLocales as $locale) {
                    $parameters['_locale'] = $locale;
                    $url->addAlternateUrl($locale, $this->router->generate($productRouteName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL));
                }
            } else {
                continue;
            }

            $sitemap->add($url);
        }
    }
}
