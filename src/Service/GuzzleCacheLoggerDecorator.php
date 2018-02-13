<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use GuzzleHttp\Promise\Promise;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Decorator for \Kevinrob\GuzzleCache\CacheMiddleware
 * Logs cache-related events, like cache hits.
 */
class GuzzleCacheLoggerDecorator
{
    /**
     * @var CacheMiddleware
     */
    private $cacheMiddleware;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(CacheMiddleware $cacheMiddleware, LoggerInterface $logger)
    {
        $this->cacheMiddleware = $cacheMiddleware;
        $this->logger = $logger;
    }

    public function __invoke(callable $handler)
    {
        $handler = ($this->cacheMiddleware)($handler);

        return function (RequestInterface $request, array $options) use (&$handler) {
            /** @var Promise $promise */
            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) use ($request): ResponseInterface {
                if ($response->hasHeader(CacheMiddleware::HEADER_CACHE_INFO)) {
                    $cacheHeader = $response->getHeaderLine(CacheMiddleware::HEADER_CACHE_INFO);
                    $requestInfo = $request->getMethod().' '.$request->getRequestTarget();
                    switch ($cacheHeader) {
                        case CacheMiddleware::HEADER_CACHE_HIT:
                            $this->logger->info('Cache hit for request: '.$requestInfo);
                            break;
                        case CacheMiddleware::HEADER_CACHE_STALE:
                            $this->logger->info('Stale response served from cache for request: '.$requestInfo);
                            break;
                    }
                }

                return $response;
            });
        };
    }
}
