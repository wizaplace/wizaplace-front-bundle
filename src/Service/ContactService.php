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

class ContactService
{
    /**
     * @var BaseService
     */
    private $discussionService;

    /**
     * @var TwigEngine
     */
    private $twigEngine;

    public function __construct(BaseService $baseService, TwigEngine $twigEngine)
    {
        $this->discussionService = $baseService;
        $this->twigEngine = $twigEngine;
    }

    /**
     * This will send an email to the marketplace's Admin.
     *
     * You need to specify the sender's email, the subject and the message. (required)
     *
     * If you have other fields, like Name or Company, you can pass them with the $extraData parameter,
     * with the fieldName as key, and fieldValue as value. (facultative)
     *
     * You can also use your own template, and pass it with the 5th parameter. (facultative)
     */
    public function contact(
        string $email,
        string $subject,
        string $message,
        array $extraData = [],
        string $templateName = '@WizaplaceFront/contact_template.html.twig'
    ): void {
        $message = $this->twigEngine->render($templateName, [
            'extraData' => $extraData,
            'message' => $message,
        ]);

        $this->discussionService->submitContactRequest($email, $subject, $message);
    }
}