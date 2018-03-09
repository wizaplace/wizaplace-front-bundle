<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Command;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wizaplace\SDK\ApiClient;
use Wizaplace\SDK\Catalog\CatalogService;

class WarmCategoryTreeCommand extends Command
{
    /**
     * @var CatalogService
     */
    private $catalogService;

    public function __construct(Client $httpClient)
    {
        parent::__construct();
        $httpClient = $this->wrapClient($httpClient);
        $this->catalogService = new CatalogService(new ApiClient($httpClient));
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->catalogService->getCategoryTree();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('wizaplace:warm:categoryTree')
            ->setDescription('Warm-up the category tree cache.');
    }

    private function wrapClient(Client $httpClient): Client
    {
        $config = $httpClient->getConfig();
        $newHandler = new HandlerStack($config['handler']);
        $newHandler->push(static function (callable $handler): callable {
            return function (RequestInterface $request, array $options) use (&$handler): Promise {
                $request = $request->withHeader('Cache-Control', 'no-cache');

                return $handler($request, $options);
            };
        });
        $config['handler'] = $newHandler;

        return new Client($config);
    }
}
