<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Service;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Wizaplace\SDK\ApiClient;

class LocaleSetter implements EventSubscriberInterface
{
    /** @var ApiClient */
    private $client;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['processLocale', 15], // must be executed after \Symfony\Component\HttpKernel\EventListener\LocaleListener
        ];
    }

    public function processLocale(GetResponseEvent $event): void
    {
        $this->client->setLanguage($event->getRequest()->getLocale());
    }
}
