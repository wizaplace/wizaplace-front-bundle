<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Controller;

use GuzzleHttp\Exception\ClientException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use Wizaplace\SDK\User\UserService;
use WizaplaceFrontBundle\Service\AuthenticationService;

class AuthController extends Controller
{
    /**
     * @var string field name to be used for the email at login
     */
    public const EMAIL_FIELD_NAME = 'email';

    /**
     * @var string field name to be used for the password at login
     */
    public const PASSWORD_FIELD_NAME = 'password';

    /**
     * @var string field name to be used for the url we wish to be redirected to after login/logout
     */
    public const REDIRECT_URL_FIELD_NAME = 'redirect_url';

    /**
     * @var string field name to be used for the CSRF token at login/logout
     */
    public const CSRF_FIELD_NAME = 'csrf_token';

    /**
     * @var string CSRF token to be used for login
     */
    public const CSRF_LOGIN_ID = 'login_token';

    /**
     * @var string CSRF token to be used for logout
     */
    public const CSRF_LOGOUT_ID = 'logout_token';

    /** @var TranslatorInterface */
    protected $translator;

    /** @var AuthenticationService */
    private $authService;

    public function __construct(TranslatorInterface $translator, AuthenticationService $authService)
    {
        $this->translator = $translator;
        $this->authService = $authService;
    }

    public function loginAction(Request $request): Response
    {
        $redirectUrl = $request->get(static::REDIRECT_URL_FIELD_NAME, null) ?? $this->generateUrl('home');

        // redirect already logged in user
        if ($this->getUser()) {
            return $this->redirect($redirectUrl);
        }

        // logging in requires an existing session
        $this->get('session')->start();

        return $this->render('@WizaplaceFront/auth/login.html.twig', [
            'redirectUrl' => $redirectUrl,
        ]);
    }

    /**
     * @deprecated use \WizaplaceFrontBundle\Service\AuthenticationService::initiateResetPasswordFromRequest instead
     */
    public function initiateResetPasswordAction(Request $request): Response
    {
        // redirection url
        $referer = $request->headers->get('referer') ?? $this->generateUrl('home');

        try {
            $form = $this->authService->getInitiateResetPasswordForm();

            $form->handleRequest($request);

            if (!$form->isSubmitted() || !$form->isValid()) {
                foreach ($form->getErrors() as $error) {
                    $this->addFlash('warning', $error->getMessage());
                }

                return $this->redirect($referer);
            }

            $this->authService->initiateResetPassword($form->getData());

            $message = $this->translator->trans('password_reset_confirmation_message');
            $this->addFlash('success', $message);
        } catch (\Throwable $e) {
            $this->addFlash('error', $this->translator->trans('@TODO'));
        }

        return $this->redirect($referer);
    }

    public function resetPasswordFormAction(string $token)
    {
        return $this->render('@WizaplaceFront/auth/reset-password.html.twig', [
            'token' => $token,
        ]);
    }

    public function submitResetPasswordAction(Request $request)
    {
        $token = $request->request->get('token');
        $newPassword = $request->request->get('newPassword');

        if (empty($token)) {
            throw new BadRequestHttpException("missing token for password reset");
        }

        if (empty($newPassword)) {
            $this->addFlash('warning', $this->translator->trans('error_new_password_required'));
        }

        try {
            $this->get(UserService::class)->changePasswordWithRecoveryToken($token, $newPassword);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                $this->addFlash('error', $this->translator->trans('invalid_password_reset_token'));

                return $this->redirectToRoute('reset_password_form', ['token' => $token]);
            }

            throw $e;
        }
        $this->addFlash('success', $this->translator->trans('password_changed'));

        return $this->redirectToRoute('login_form');
    }
}
