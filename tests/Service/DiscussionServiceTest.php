<?php

/**
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Service;

use DG\BypassFinals;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Wizaplace\SDK\ApiClient;
use PHPUnit\Framework\TestCase;
use Wizaplace\SDK\Discussion\DiscussionService;
use WizaplaceFrontBundle\Service\ContactService;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class DiscussionServiceTest extends TestCase
{
    public function setUp()
    {
        // Thanks the people who set... a service class ... final ... without an interface. So otherwise, no mock.
        BypassFinals::enable();
    }

    public function testContact()
    {
        // Content of the message.
        $email = 'john.doe@email.com';
        $subject = 'I have a request';
        $message = 'I would like to contact an admin';
        $extraData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
        ];
        $body = '<p>Helloooo, itsss meeeee</p>';

        // Twig
        $twigService = $this->createMock(TwigEngine::class);
        $twigService->expects(static::once())->method('render')
            ->with(
                '@WizaplaceFront/contact_template.html.twig',
                [
                    'extraData' => $extraData,
                    'message' => $message,
                ]
            )
            ->willReturn($body);

        // SDK
        $discussionService = static::createMock(DiscussionService::class);
        $discussionService->expects(static::once())->method('submitContactRequest')
            ->with(
                $email,
                $subject,
                $body
            );

        // And finally the test itself...
        $contactService = new ContactService($discussionService, $twigService);
        $contactService->contact(
            $email,
            $subject,
            $message,
            $extraData
        );
    }
}
