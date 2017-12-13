<?php
/**
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);


namespace WizaplaceFrontBundle\Tests\Service;

use Wizaplace\SDK\ApiClient;
use WizaplaceFrontBundle\Service\DiscussionService;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class DiscussionServiceTest extends BundleTestCase
{
    public function testContact()
    {
        $container = self::$kernel->getContainer();
        $apiClient = $container->get(ApiClient::class);

        $baseService = new \Wizaplace\SDK\Discussion\DiscussionService($apiClient);
        $loader = new \Twig_Loader_Filesystem();
        $twigEnvironment = new \Twig_Environment($loader, []);
        $discussionService = new DiscussionService($baseService, $twigEnvironment);

        $discussionService->contact('john.doe@email.com', 'I have a request', 'I would like to contact an admin', [
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);
    }
}
