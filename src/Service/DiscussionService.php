<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Service;

use Symfony\Bridge\Twig\TwigEngine;
use \Wizaplace\SDK\Discussion\DiscussionService as BaseService;

class DiscussionService
{
    /**
     * @var BaseService
     */
    private $discussionService;

    /**
     * @var \Twig_Environment
     */
    private $environment;

    public function __construct(BaseService $baseService, TwigEngine $environment)
    {
        $this->discussionService = $baseService;
        $this->environment = $environment;
    }

    public function contact(
        string $email,
        string $subject,
        string $message,
        array $extraData = [],
        string $templateName = '@WizaplaceFront/contact_template.html.twig'
    ): void {
        $message = $this->environment->render($templateName, [
            'extraData' => $extraData,
            'message' => $message,
        ]);

        $this->discussionService->submitContactRequest($email, $subject, $message);
    }
}
