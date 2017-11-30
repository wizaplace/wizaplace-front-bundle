<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);


namespace WizaplaceFrontBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\Order\OrderService;

class InvoiceService
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function downloadPdf(int $orderId): Response
    {
        $pdf = $this->orderService->downloadPdfInvoice($orderId);

        $response = new Response($pdf->getContents());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment');

        return $response;
    }
}
