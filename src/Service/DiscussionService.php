<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Service;

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

    public function __construct(BaseService $baseService, \Twig_Environment $environment)
    {
        $this->discussionService = $baseService;
        $this->environment = $environment;
        $loader = new \Twig_Loader_Filesystem('src/Resources/views');
        $this->environment->setLoader($loader);
    }

    /**
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function contact(
        string $email,
        string $subject,
        string $message,
        array $extraData = [],
        string $templateName = 'contact_template.html.twig'
    ) : void
    {
        $message = $this->environment->render($templateName, [
            'extraData' => $extraData,
            'message' => $message,
        ]);

        var_dump($message);

        $this->discussionService->submitContactRequest($email, $subject, $message);
    }
}
