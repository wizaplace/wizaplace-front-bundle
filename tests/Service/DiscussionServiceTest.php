<?php
/**
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);


namespace WizaplaceFrontBundle\Tests\Service;

use WizaplaceFrontBundle\Service\DiscussionService;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class DiscussionServiceTest extends BundleTestCase
{
    public function testContact()
    {
        $container = self::$kernel->getContainer();

        $discussionService = $container->get(DiscussionService::class);

        $discussionService->contact('john.doe@email.com', 'I have a request', 'I would like to contact an admin', [
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $this->assertTrue(true);
    }
}
