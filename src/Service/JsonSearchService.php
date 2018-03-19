<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Serializer;
use Wizaplace\SDK\Catalog\CatalogService;
use Wizaplace\SDK\Catalog\GeoFilter;
use Wizaplace\SDK\Catalog\SearchResult;

final class JsonSearchService
{
    /**
     * @var CatalogService
     */
    private $catalogService;

    public function __construct(CatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    /**
     * @see \Wizaplace\SDK\Catalog\CatalogService::search
     * @return string JSON-encoded \Wizaplace\SDK\Catalog\SearchResult
     */
    public function search(
        string $query = '',
        array $filters = [],
        array $sorting = [],
        int $resultsPerPage = 12,
        int $page = 1,
        ?GeoFilter $geoFilter = null
    ): string {
        $result = $this->catalogService->search($query, $filters, $sorting, $resultsPerPage, $page, $geoFilter);

        return self::jsonEncodeSearchResult($result);
    }

    private static function jsonEncodeSearchResult(SearchResult $result): string
    {
        static $serializer;
        if (!isset($serializer)) {
            $serializer = new Serializer(
                [
                    new DateTimeNormalizer(\DateTime::RFC3339),
                    new JsonSerializableNormalizer(),
                    new GetSetMethodNormalizer(),
                ],
                [
                    new JsonEncoder(),
                ]
            );
        }


        return $serializer->serialize($result, JsonEncoder::FORMAT);
    }
}
