# Paylink Package

This package enables seamless integration with the Paylink payment gateway within PHP applications. and provides convenient methods to interact with the Paylink API, facilitating payment processing and related functionalities.

## Installation

You can install the `paylinksa/php` package via composer. Run the following command in your terminal:

```bash
composer require paylinksa/php
```

## Environment Setup

Create an instance of Paylink based on your environment

- For Testing

```php
use Paylink\Paylink;

$paylink = Paylink::test();
```

- For Production

```php
use Paylink\Paylink;

$paylink = Paylink::production('API_ID_xxxxxxxxxx', 'SECRET_KEY_xxxxxxxxxx');
```

## Methods

1. **Add Invoice**:

   Add an invoice to the system for payment processing.

   ```php
      use Paylink\Models\PaylinkProduct;

      $invoiceDetails = $paylink->addInvoice(
         amount: 250.0,
         clientMobile: '0512345678',
         clientName: 'Mohammed Ali',
         orderNumber: '123456789',
         products: [
            new PaylinkProduct(title: 'item1', price: 5.0, qty: 10),
            new PaylinkProduct(title: 'item2', price: 20.0, qty: 10)
         ],
         callBackUrl: 'https://example.com',
      );
   ```

2. **Get Invoice**

   Retrieve invoice details.

   ```php
      $invoiceDetails = $paylink->getInvoice(transactionNo: '1714289084591');

      // $invoiceDetails->orderStatus;
      // $invoiceDetails->transactionNo;
      // $invoiceDetails->url;
      // ...
   ```

3. **Cancel Invoice**

   Cancel an existing invoice initiated by the merchant.

   ```php
      $paylink->cancelInvoice(transactionNo: '1714289084591'); // true-false
   ```

## Examples:

- [Paylink Payment Examples](Examples/PaymentExamples.php)

For detailed usage instructions, refer to the [Paylink Payment Documentation](docs/Paylink.md)

---

## Support

If you encounter any issues or have questions about the Paylink Package, please [contact us](https://paylink.sa/).

## License

This package is open-source software licensed under the [MIT license](LICENSE).
