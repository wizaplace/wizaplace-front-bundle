<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\Catalog\CatalogService;
use Wizaplace\SDK\Catalog\Category;
use Wizaplace\SDK\Seo\SeoService;
use Wizaplace\SDK\Seo\SlugTargetType;
use WizaplaceFrontBundle\Service\FavoriteService;

class CategoryController extends Controller
{
    /** @var SeoService */
    protected $seoService;

    /** @var CatalogService */
    protected $catalogService;

    /** @var FavoriteService */
    protected $favoriteService;

    public function __construct(SeoService $seoService, CatalogService $catalogService, FavoriteService $favoriteService)
    {
        $this->seoService = $seoService;
        $this->catalogService = $catalogService;
        $this->favoriteService = $favoriteService;
    }

    public function viewAction(string $slug) : Response
    {
        $currentCategory = $this->getCategoryFromSlug($slug);
        if (!$currentCategory) {
            throw $this->createNotFoundException("Category '${slug}' Not Found");
        }

        $filters = [
            'categories' => $currentCategory->getId(),
        ];

        $userFavoriteIds = $this->favoriteService->getFavoriteIds();

        return $this->render('@WizaplaceFront/search/search.html.twig', [
            'currentCategory' => $currentCategory,
            'filters' => $filters,
            'userFavoriteIds' => $userFavoriteIds,
        ]);
    }

    protected function getCategoryFromSlug(string $slug): ?Category
    {
        $slugTarget = $this->seoService->resolveSlug($slug);
        if (is_null($slugTarget) || !$slugTarget->getObjectType()->equals(SlugTargetType::CATEGORY())) {
            return null;
        }
        $categoryId = (int) $slugTarget->getObjectId();

        return $this->catalogService->getCategory($categoryId);
    }
}
