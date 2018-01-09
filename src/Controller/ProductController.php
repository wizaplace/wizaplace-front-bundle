<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\Catalog\CatalogService;
use Wizaplace\SDK\Catalog\DeclinationId;
use Wizaplace\SDK\Catalog\Option;
use Wizaplace\SDK\Catalog\OptionVariant;
use Wizaplace\SDK\Catalog\Product;
use Wizaplace\SDK\Catalog\ProductCategory;
use Wizaplace\SDK\Catalog\Review\ReviewService;
use Wizaplace\SDK\Exception\NotFound;
use Wizaplace\SDK\Favorite\FavoriteService;
use Wizaplace\SDK\Seo\SeoService;
use Wizaplace\SDK\Seo\SlugTargetType;
use WizaplaceFrontBundle\Service\ProductListService;
use WizaplaceFrontBundle\Service\ProductUrlGenerator;

class ProductController extends Controller
{
    /** @var ProductUrlGenerator */
    protected $productUrlGenerator;

    /** @var SeoService */
    protected $seoService;

    /** @var CatalogService */
    protected $catalogService;

    /** @var ProductListService */
    protected $productListService;

    /** @var ReviewService */
    protected $reviewService;

    /** @var FavoriteService */
    protected $favoriteService;

    public function __construct(
        ProductUrlGenerator $productUrlGenerator,
        SeoService $seoService,
        CatalogService $catalogService,
        ProductListService $productListService,
        ReviewService $reviewService,
        FavoriteService $favoriteService
    ) {
        $this->productUrlGenerator = $productUrlGenerator;
        $this->seoService = $seoService;
        $this->catalogService = $catalogService;
        $this->productListService = $productListService;
        $this->reviewService = $reviewService;
        $this->favoriteService = $favoriteService;
    }

    public function viewAction(string $categoryPath, string $slug, Request $request): Response
    {
        $product = $this->getProductFromSlug($slug);
        if (!$product) {
            throw $this->createNotFoundException("Product '${slug}' Not Found");
        }

        // Recovering the declinationId from url, if none passed, declination = first declination of the product
        if (!$request->query->has('d')) {
            $declinationId = $product->getDeclinations()[0]->getId();
        } else {
            $declinationId = new DeclinationId($request->query->get('d'));
        }

        $realCategoryPath = implode('/', array_map(function (ProductCategory $category) : string {
            return $category->getSlug();
        }, $product->getCategoryPath()));
        if ($categoryPath !== $realCategoryPath) {
            return $this->redirect($this->productUrlGenerator->generateUrlFromProduct($product));
        }

        // latestProducts
        $latestProducts = $this->productListService->getLatestProducts(6);

        //product Reviews
        $reviews = $this->reviewService->getProductReviews($product->getId());

        $declination = $product->getDeclination($declinationId);
        $variantIdByOptionId = [];
        foreach ($declination->getOptions() as $option) {
            $variantIdByOptionId[$option->getId()] = $option->getVariantId();
        }

        $options = array_map(function (Option $option) use ($product, $variantIdByOptionId) : array {
            return [
                'id' => $option->getId(),
                'name' => $option->getName(),
                'variants' => array_map(function (OptionVariant $variant) use ($product, $option, $variantIdByOptionId) : array {
                    $isSelected = false;
                    if (isset($variantIdByOptionId[$option->getId()])) {
                        $isSelected = $variantIdByOptionId[$option->getId()] === $variant->getId();
                    }
                    $variantIdByOptionId[$option->getId()] = $variant->getId();
                    $declinationId = $product->getDeclinationFromOptions($variantIdByOptionId)->getId();

                    return [
                        'id' => $variant->getId(),
                        'name' => $variant->getName(),
                        'selected' => $isSelected,
                        'url' => $this->productUrlGenerator->generateUrlFromProduct($product, $declinationId),
                        'image' => $variant->getImage(),
                    ];
                }, $option->getVariants()),
            ];
        }, $product->getOptions());

        $isFavorite = false;
        if ($this->getUser() !== null) {
            $isFavorite = $this->favoriteService->isInFavorites($declinationId);
        }

        return $this->render('@WizaplaceFront/product/product.html.twig', [
            'product' => $product,
            'latestProducts' => $latestProducts,
            'reviews' => $reviews,
            'declination' => $declination,
            'options' => $options,
            'isFavorite' => $isFavorite,
        ]);
    }

    public function previewAction(string $productId)
    {
        try {
            $product = $this->get(CatalogService::class)->getProductById($productId);
        } catch (NotFound $e) {
            throw $this->createNotFoundException("Product '${productId}' Not Found");
        }

        return $this->redirect($this->get(ProductUrlGenerator::class)->generateUrlFromProduct($product));
    }

    protected function getProductFromSlug(string $slug): ?Product
    {
        $slugTarget = $this->seoService->resolveSlug($slug);
        if (is_null($slugTarget) || !$slugTarget->getObjectType()->equals(SlugTargetType::PRODUCT())) {
            return null;
        }
        $productId = $slugTarget->getObjectId();

        return $this->catalogService->getProductById($productId);
    }
}
