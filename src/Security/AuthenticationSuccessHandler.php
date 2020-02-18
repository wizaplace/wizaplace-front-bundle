<?php

/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */

declare(strict_types=1);

namespace WizaplaceFrontBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Translation\TranslatorInterface;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /** @var FlashBagInterface */
    private $flashBag;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(HttpUtils $httpUtils, array $options, FlashBagInterface $flashBag, TranslatorInterface $translator)
    {
        $this->flashBag = $flashBag;
        $this->translator = $translator;
        parent::__construct($httpUtils, $options);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $this->flashBag->add('success', $this->translator->trans('auth.login.notification.success'));

        return parent::onAuthenticationSuccess($request, $token);
    }
}
