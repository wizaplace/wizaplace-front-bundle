<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Service;

use Symfony\Component\HttpFoundation\Request;
use WizaplaceFrontBundle\Service\AuthenticationService;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class AuthenticationServiceTest extends BundleTestCase
{
    public function testInitiateResetPassword()
    {
        $container = self::$kernel->getContainer();
        $authService = $container->get(AuthenticationService::class);

        $form = $authService->getInitiateResetPasswordForm();

        $csrfToken = $form->createView()['csrf_token']->vars['value'];

        $request = Request::create('does-not-matter', 'POST', [
            'csrf_token' => $csrfToken,
            'email' => 'user@wizaplace.com',
        ]);

        $form = $authService->getInitiateResetPasswordForm();

        $form->handleRequest($request);

        self::assertTrue($form->isSubmitted());
        self::assertTrue($form->isValid());

        $authService->initiateResetPassword($form->getData());
    }

    public function testInitiateResetPasswordWithoutEmail()
    {
        $container = self::$kernel->getContainer();
        $authService = $container->get(AuthenticationService::class);

        $form = $authService->getInitiateResetPasswordForm();

        $csrfToken = $form->createView()['csrf_token']->vars['value'];

        $request = Request::create('does-not-matter', 'POST', [
            'csrf_token' => $csrfToken,
        ]);

        $form = $authService->getInitiateResetPasswordForm();

        $form->handleRequest($request);

        self::assertFalse($form->isSubmitted());
        self::assertNull($form->getData());
    }

    public function testInitiateResetPasswordWithoutCsrf()
    {
        $container = self::$kernel->getContainer();
        $authService = $container->get(AuthenticationService::class);

        $form = $authService->getInitiateResetPasswordForm();

        $request = Request::create('does-not-matter', 'POST', [
            'email' => 'user@wizaplace.com',
        ]);

        $form = $authService->getInitiateResetPasswordForm();

        $form->handleRequest($request);

        self::assertTrue($form->isSubmitted());
        self::assertFalse($form->isValid());

        // the service can't check the csrf (and it's not its role), so this works
        $authService->initiateResetPassword($form->getData());
    }
}
