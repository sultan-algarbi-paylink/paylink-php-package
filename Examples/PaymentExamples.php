<?php

require 'vendor/autoload.php';

// Import Paylink Package
use Paylink\Paylink;
use Paylink\Models\PaylinkProduct;

function addInvoice()
{
    try {
        echo nl2br("\n==================== Add Invoice ====================\n");

        // Create an instance of Paylink
        $paylink = Paylink::test();
        // $paylink = Paylink::production('API_ID_xxxxxxxxxx', 'SECRET_KEY_xxxxxxxxxx');

        // Prepare products as PaylinkProduct objects
        $products = [
            new PaylinkProduct(
                title: 'Book',
                price: 50.0,
                qty: 2,
                description: null, // optional
                isDigital: false, // optional
                imageSrc: null, // optional
                specificVat: null, // optional
                productCost: null, // optional
            ),
            new PaylinkProduct(
                title: 'Pen',
                price: 7.0,
                qty: 10,
            )
        ];

        // Call Paylink to add a new invoice
        $invoiceDetails = $paylink->addInvoice(
            amount: 170.0,
            clientMobile: '0512345678',
            clientName: 'Mohammed Ali',
            orderNumber: '123456789',
            products: $products,
            callBackUrl: 'https://example.com',
            cancelUrl: 'https://example.com', // optional
            clientEmail: 'mohammed@test.com', // optional
            currency: 'SAR', // optional
            note: 'Test invoice', // optional
            smsMessage: 'URL: [SHORT_URL], Amount: [AMOUNT]', // optional
            supportedCardBrands: ['mada', 'visaMastercard', 'amex', 'tabby', 'tamara', 'stcpay', 'urpay'], // optional
            displayPending: true, // optional
        );

        echo nl2br("orderStatus: {$invoiceDetails->orderStatus}\n");
        echo nl2br("transactionNo: {$invoiceDetails->transactionNo}\n");
        echo nl2br("payment url: {$invoiceDetails->url}\n");
        // ...

    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function getInvoice()
{
    try {
        echo nl2br("\n==================== Get Invoice ====================\n");

        // Create an instance of Paylink
        $paylink = Paylink::production('API_ID_xxxxxxxxxx', 'SECRET_KEY_xxxxxxxxxx');

        // Call Paylink to get the invoice
        $invoiceDetails = $paylink->getInvoice(transactionNo: '1714289084591');

        echo nl2br("orderStatus: {$invoiceDetails->orderStatus}\n");
        echo nl2br("transactionNo: {$invoiceDetails->transactionNo}\n");
        echo nl2br("payment url: {$invoiceDetails->url}\n");
        // ...

    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function cancelInvoice()
{
    try {
        echo nl2br("\n==================== Cancel Invoice ====================\n");
        // Create an instance of Paylink
        $paylink = Paylink::test();

        // Call Paylink to cancel the invoice
        $deleted = $paylink->cancelInvoice(transactionNo: '1714289084591');

        // -- If no error exception is thrown, the invoice was canceled successfully
        echo $deleted ? 'Invoice canceled successfully' : 'Failed to cancel invoice';
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

addInvoice();
getInvoice();
cancelInvoice();
