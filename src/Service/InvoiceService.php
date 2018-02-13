<?php
/**
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);


namespace WizaplaceFrontBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use Wizaplace\SDK\Order\OrderService;

class InvoiceService
{
    /**
     * @var OrderService
     */
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @throws \Wizaplace\SDK\Authentication\AuthenticationRequired
     */
    public function downloadPdf(int $orderId, string $filename = "Invoice"): Response
    {
        $pdf = $this->orderService->downloadPdfInvoice($orderId);

        $response = new Response($pdf->getContents());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename: "'.$filename.'.pdf"');

        return $response;
    }
}
