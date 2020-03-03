<?php

/**
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Service;

use Wizaplace\SDK\ApiClient;
use Wizaplace\SDK\Catalog\DeclinationId;
use Wizaplace\SDK\Order\OrderService;
use WizaplaceFrontBundle\Service\BasketService;
use WizaplaceFrontBundle\Service\InvoiceService;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class InvoiceServiceTest extends BundleTestCase
{
    public function testDownloadPdf()
    {
        $container = self::$kernel->getContainer();
        $apiClient = $container->get(ApiClient::class);
        $apiClient->authenticate('customer-1@world-company.com', 'password-customer-1');

        $basketService = new BasketService(new \Wizaplace\SDK\Basket\BasketService($apiClient), $container->get('session'));
        $orderService = new OrderService($apiClient);

        $basketService->addProductToBasket(new DeclinationId('1'), 1);
        $basket = $basketService->getBasket();

        $shippings = [];
        foreach ($basket->getCompanyGroups() as $companyGroup) {
            foreach ($companyGroup->getShippingGroups() as $shippingGroup) {
                $availableShippings = $shippingGroup->getShippings();
                $shippings[$shippingGroup->getId()] = end($availableShippings)->getId();
            }
        }
        $basketService->selectShippings($shippings);

        $availablePayments = $basketService->getPayments();
        $selectedPayment = reset($availablePayments)->getId();
        $redirectUrl = 'https://demo.loc/order/confirm';

        $paymentInformation = $basketService->checkout($selectedPayment, true, $redirectUrl);
        $orders = $paymentInformation->getOrders();
        $order = $orderService->getOrder($orders[0]->getId());

        $invoiceService = new InvoiceService($orderService);
        $response = $invoiceService->downloadPdf($order->getId());
        self::assertSame('application/pdf', $response->headers->get('content-type'));
        self::assertSame('attachment; filename: "Invoice.pdf"', $response->headers->get('content-disposition'));
        $pdfHeader = '%PDF-1.4';
        $pdfContent = $response->getContent();
        self::assertStringStartsWith($pdfHeader, $pdfContent);
        self::assertGreaterThan(\strlen($pdfHeader), \strlen($pdfContent));
    }
}
