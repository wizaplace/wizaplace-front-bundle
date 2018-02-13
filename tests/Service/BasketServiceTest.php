<?php
/**
 * @copyright Copyright (c) Wizacha
 * @license Proprietary
 */
declare(strict_types=1);

namespace WizaplaceFrontBundle\Tests\Service;

use Wizaplace\SDK\Basket\Basket;
use Wizaplace\SDK\Catalog\DeclinationId;
use Wizaplace\SDK\Order\OrderService;
use Wizaplace\SDK\Order\OrderStatus;
use WizaplaceFrontBundle\Service\AuthenticationService;
use WizaplaceFrontBundle\Tests\BundleTestCase;

class BasketServiceTest extends BundleTestCase
{
    public function testFullCheckout()
    {
        $container = self::$kernel->getContainer();
        $container->get(AuthenticationService::class)->authenticate('customer-1@world-company.com', 'password-customer-1');

        $basketService = $container->get('test.WizaplaceFrontBundle\Service\BasketService');
        $orderService = $container->get(OrderService::class);

        $basket = $basketService->getBasket();
        self::assertInstanceOf(Basket::class, $basket);
        self::assertNotEmpty($basket->getId());

        $newQuantity = $basketService->addProductToBasket(new DeclinationId('1'), 1);
        self::assertSame(1, $newQuantity);

        $newQuantity = $basketService->addProductToBasket(new DeclinationId('1'), 1);
        self::assertSame(2, $newQuantity);

        $basket = $basketService->getBasket();
        self::assertNotNull($basket);

        $shippings = [];
        foreach ($basket->getCompanyGroups() as $companyGroup) {
            self::assertGreaterThan(0, $companyGroup->getCompany()->getId());
            self::assertNotEmpty($companyGroup->getCompany()->getName());
            self::assertNotEmpty($companyGroup->getCompany()->getSlug());

            foreach ($companyGroup->getShippingGroups() as $shippingGroup) {
                self::assertGreaterThan(0, $shippingGroup->getId());

                $availableShippings = $shippingGroup->getShippings();
                $shippings[$shippingGroup->getId()] = end($availableShippings)->getId();

                foreach ($shippingGroup->getItems() as $basketItem) {
                    // Here we mostly check the items were properly unserialized
                    self::assertGreaterThan(0, $basketItem->getProductId());
                    self::assertGreaterThan(0, $basketItem->getQuantity());
                    self::assertNotEmpty($basketItem->getProductName());
                    self::assertNotEmpty($basketItem->getDeclinationId());
                    self::assertSame([], $basketItem->getDeclinationOptions());
                    self::assertGreaterThan(0, $basketItem->getIndividualPrice());
                    self::assertGreaterThanOrEqual($basketItem->getIndividualPrice(), $basketItem->getTotal());
                    $basketItem->getMainImage();
                    $basketItem->getCrossedOutPrice();
                }
            }
        }
        $basketService->selectShippings($shippings);

        $availablePayments = $basketService->getPayments();
        foreach ($availablePayments as $availablePayment) {
            // Here we mostly check the payments were properly unserialized
            $availablePayment->getImage();
            $availablePayment->getDescription();
            self::assertNotEmpty($availablePayment->getName());
            self::assertGreaterThan(0, $availablePayment->getId());
            self::assertGreaterThanOrEqual(0, $availablePayment->getPosition());
        }
        $selectedPayment = reset($availablePayments)->getId();
        $redirectUrl = 'https://demo.loc/order/confirm';

        $paymentInformation = $basketService->checkout($selectedPayment, true, $redirectUrl);

        // @TODO : check that the two following values are normal
        self::assertSame('', $paymentInformation->getHtml());
        self::assertNull($paymentInformation->getRedirectUrl());

        $orders = $paymentInformation->getOrders();
        self::assertCount(1, $orders);

        $order = $orderService->getOrder($orders[0]->getId());
        self::assertSame($orders[0]->getId(), $order->getId());
        self::assertSame(3, $order->getCompanyId());
        self::assertSame('Colissmo', $order->getShippingName());
        self::assertEquals(OrderStatus::STANDBY_BILLING(), $order->getStatus());
        self::assertGreaterThan(1500000000, $order->getTimestamp()->getTimestamp());
        self::assertSame(135.8, $order->getTotal());
        self::assertSame(135.8, $order->getSubtotal());
        self::assertSame('40 rue Laure Diebold', $order->getShippingAddress()->getAddress());

        $orderItems = $order->getOrderItems();
        self::assertCount(1, $orderItems);
        self::assertTrue((new DeclinationId('1_0'))->equals($orderItems[0]->getDeclinationId()));
        self::assertSame('Z11 Plus Boîtier PC en Acier ATX', $orderItems[0]->getProductName());
        self::assertSame('978020137962', $orderItems[0]->getProductCode());
        self::assertSame(67.9, $orderItems[0]->getPrice());
        self::assertSame(2, $orderItems[0]->getAmount());
    }

    public function testBasketIsSavedInUserAccount(): void
    {
        $container = self::$kernel->getContainer();

        $container->get(AuthenticationService::class)->authenticate('customer-1@world-company.com', 'password-customer-1');

        $basketId =  $container->get('test.WizaplaceFrontBundle\Service\BasketService')->getBasket()->getId();

        // Simulate a new browsing session : reboot the kernel, clear the session
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $container->get('session')->clear();

        $container->get(AuthenticationService::class)->authenticate('customer-1@world-company.com', 'password-customer-1');

        $newBasketId =  $container->get('test.WizaplaceFrontBundle\Service\BasketService')->getBasket()->getId();

        self::assertSame($basketId, $newBasketId);
    }

    public function testBasketsMergeOnAuthentication(): void
    {
        $container = self::$kernel->getContainer();

        // create a basket associated to my account
        $container->get(AuthenticationService::class)->authenticate('customer-1@world-company.com', 'password-customer-1');
        $container->get('test.WizaplaceFrontBundle\Service\BasketService')->addProductToBasket(new DeclinationId('1_0'), 1);
        $container->get('test.WizaplaceFrontBundle\Service\BasketService')->addProductToBasket(new DeclinationId('3_8_7'), 1);
        $authenticatedBasketId =  $container->get('test.WizaplaceFrontBundle\Service\BasketService')->getBasket()->getId();

        // Simulate a new browsing session : reboot the kernel, clear the session
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $container->get('session')->clear();

        // create a basket without account
         $container->get('test.WizaplaceFrontBundle\Service\BasketService')->addProductToBasket(new DeclinationId('1_0'), 2);
         $container->get('test.WizaplaceFrontBundle\Service\BasketService')->addProductToBasket(new DeclinationId('3_8_8'), 1);
        $anonymousBasketId =  $container->get('test.WizaplaceFrontBundle\Service\BasketService')->getBasket()->getId();
        self::assertNotEquals($authenticatedBasketId, $anonymousBasketId);

        // login, which should trigger the basket merge
        $container->get(AuthenticationService::class)->authenticate('customer-1@world-company.com', 'password-customer-1');
        self::assertSame($authenticatedBasketId, $container->get('test.WizaplaceFrontBundle\Service\BasketService')->getBasket()->getId());

        // check that the authenticated basket was properly affected by the merge
        $mergedBasket =  $container->get('test.WizaplaceFrontBundle\Service\BasketService')->getBasket();
        $quantitiesMap = [];
        foreach ($mergedBasket->getCompanyGroups() as $companyGroup) {
            foreach ($companyGroup->getShippingGroups() as $shippingGroup) {
                foreach ($shippingGroup->getItems() as $item) {
                    $quantitiesMap[(string) $item->getDeclinationId()] = $item->getQuantity();
                }
            }
        }

        self::assertSame([
            '1_0' => 2,
            '3_8_7' => 1,
            '3_8_8' => 1,
        ], $quantitiesMap);
    }
}
