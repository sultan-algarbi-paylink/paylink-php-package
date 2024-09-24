<?php

// Import Paylink Package
use Paylink\Paylink;
use Paylink\Models\PaylinkProduct;

function addInvoice()
{
    try {
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

        echo $invoiceDetails->orderStatus;
        echo $invoiceDetails->transactionNo;
        echo $invoiceDetails->url;
        // ...

    } catch (Exception $e) {
        // -- Handle the error
    }
}

function getInvoice()
{
    try {
        // Create an instance of Paylink
        $paylink = Paylink::production('API_ID_xxxxxxxxxx', 'SECRET_KEY_xxxxxxxxxx');

        // Call Paylink to get the invoice
        $invoiceDetails = $paylink->getInvoice(transactionNo: '1714289084591');

        echo $invoiceDetails->orderStatus;
        echo $invoiceDetails->transactionNo;
        echo $invoiceDetails->url;
        // ...

    } catch (Exception $e) {
        // -- Handle the error
    }
}

function cancelInvoice()
{
    try {
        // Create an instance of Paylink
        $paylink = Paylink::test();

        // Call Paylink to cancel the invoice
        $deleted = $paylink->cancelInvoice(
            transactionNo: '1714289084591'
        );

        // -- If no error exception is thrown, the invoice was canceled successfully
        if ($deleted) {
            return 'Invoice canceled successfully';
        } else {
            return 'Failed to cancel invoice';
        }
    } catch (Exception $e) {
        // -- Handle the error
    }
}
