<?php

namespace Paylink\Services;

use Exception;
use Paylink\Models\PaylinkProduct;
use Paylink\Models\PaylinkInvoiceResponse;

class PaylinkService
{
    // API URLs for production and test environments
    private const PRODUCTION_API_URL = 'https://restapi.paylink.sa';
    private const TEST_API_URL = 'https://restpilot.paylink.sa';

    // Payment Page URLs for production and test environments
    private const PRODUCTION_PAYMENT_PAGE_URL = 'https://payment.paylink.sa/pay/order';
    private const TEST_PAYMENT_PAGE_URL = 'https://paymentpilot.paylink.sa/pay/info';

    // Default credentials for the test environment
    private const DEFAULT_TEST_API_ID = 'APP_ID_1123453311';
    private const DEFAULT_TEST_SECRET_KEY = '0662abb5-13c7-38ab-cd12-236e58f43766';

    // Valid card brands accepted by Paylink.
    private const VALID_CARD_BRANDS = ['mada', 'visaMastercard', 'amex', 'tabby', 'tamara', 'stcpay', 'urpay'];

    // Properties
    private string $apiBaseUrl;
    private string $paymentBaseUrl;
    private string $apiId;
    private string $secretKey;
    private bool $persistToken = false;
    private ?string $idToken;

    /**
     * PaylinkService constructor.
     *
     * @param string $environment
     * @param string|null $apiId
     * @param string|null $secretKey
     */
    public function __construct(string $environment, ?string $apiId = null, ?string $secretKey = null)
    {
        // Determine the base URL based on the environment
        $this->apiBaseUrl = $environment === 'production' ? self::PRODUCTION_API_URL : self::TEST_API_URL;
        $this->paymentBaseUrl = $environment === 'production' ? self::PRODUCTION_PAYMENT_PAGE_URL : self::TEST_PAYMENT_PAGE_URL;

        // Determine API ID and Secret Key
        $this->apiId = $environment === 'production' ? $apiId : self::DEFAULT_TEST_API_ID;
        $this->secretKey = $environment === 'production' ? $secretKey : self::DEFAULT_TEST_SECRET_KEY;
        $this->idToken = null;

        if (is_null($this->apiId) || is_null($this->secretKey)) {
            throw new \InvalidArgumentException('API_ID and Secret_Key are required for the production environment');
        }
    }

    /**
     * Initialize the Paylink client for the test environment.
     *
     * @return static
     */
    public static function test(): self
    {
        return new self('test');
    }

    /**
     * Initialize the Paylink client for the production environment.
     *
     * @param string $apiId
     * @param string $secretKey
     * @return static
     */
    public static function production(string $apiId, string $secretKey): self
    {
        return new self('production', $apiId, $secretKey);
    }

    /**
     * Authenticates with the Paylink API and retrieves an authentication token.
     *
     * @throws Exception If authentication fails or if the token is not found in the response.
     */
    private function authentication()
    {
        try {
            // Request Endpoint
            $requestEndpoint = "{$this->apiBaseUrl}/api/auth";

            // Request headers
            $requestHeaders = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ];

            // Request body parameters
            $requestBody = [
                'apiId' => $this->apiId,
                'secretKey' => $this->secretKey,
                'persistToken' => $this->persistToken,
            ];

            // Send a POST request to the authentication endpoint
            $response = $this->httpRequest(
                'POST',
                $requestEndpoint,
                $requestHeaders,
                $requestBody,
                'Failed to authenticate'
            );

            // Extract the token
            if (empty($response['id_token'])) {
                throw new Exception('Authentication token missing in the response.');
            }

            // Store the token for future API calls
            $this->idToken = $response['id_token'];
        } catch (Exception $e) {
            // In case of any exception, clear the token and rethrow the error
            $this->idToken = null;
            throw $e;
        }
    }

    /** --------------------------------------------- Invoice Operations --------------------------------------------- */

    /**
     * Adds an invoice to the Paylink system.
     *
     * @param float $amount The total amount of the invoice.
     * @param string $clientMobile The mobile number of the client.
     * @param string $clientName The name of the client.
     * @param string $orderNumber A unique identifier for the invoice.
     * @param PaylinkProduct[] $products An array of PaylinkProduct objects to be included in the invoice.
     * @param string $callBackUrl Call back URL that will be called by Paylink to the merchant system.
     * @param string|null $cancelUrl Call back URL to cancel orders.
     * @param string|null $clientEmail The email address of the client.
     * @param string|null $currency The currency code of the invoice. The default value is SAR.
     * @param string|null $note A note for the invoice.
     * @param string|null $smsMessage This option will enable the invoice to be sent to the client's mobile.
     * @param array|null $supportedCardBrands List of supported card brands.
     * @param bool|null $displayPending This option will make this invoice displayed in my.paylink.sa.
     *
     * @return PaylinkInvoiceResponse Returns the details of the added invoice.
     * @throws Exception If adding the invoice fails.
     */
    public function addInvoice(
        float $amount,
        string $clientMobile,
        string $clientName,
        string $orderNumber,
        array $products,
        string $callBackUrl,
        ?string $cancelUrl = null,
        ?string $clientEmail = null,
        ?string $currency = 'SAR',
        ?string $note = null,
        ?string $smsMessage = null,
        ?array $supportedCardBrands = [],
        ?bool $displayPending = true
    ): PaylinkInvoiceResponse {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->authentication();
            }

            // Filter and sanitize supportedCardBrands
            $filteredCardBrands = array_filter($supportedCardBrands, function ($brand): bool {
                return is_string($brand) && in_array($brand, self::VALID_CARD_BRANDS);
            });

            // Convert PaylinkProduct objects to arrays
            $productsArray = [];
            foreach ($products as $index => $product) {
                if ($product instanceof PaylinkProduct) {
                    $productsArray[] = $product->toArray();
                } else {
                    throw new \InvalidArgumentException("Invalid product type at index {$index}, Each product must be an instance of Paylink\Models\PaylinkProduct.");
                }
            }

            // Request Endpoint
            $requestEndpoint = "{$this->apiBaseUrl}/api/addInvoice";

            // Request headers
            $requestHeaders = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->idToken}",
            ];

            // Request body parameters
            $requestBody = [
                'amount' => $amount,
                'callBackUrl' => $callBackUrl,
                'cancelUrl' => $cancelUrl,
                'clientEmail' => $clientEmail,
                'clientMobile' => $clientMobile,
                'currency' => $currency,
                'clientName' => $clientName,
                'note' => $note,
                'orderNumber' => $orderNumber,
                'products' => $productsArray,
                'smsMessage' => $smsMessage,
                'supportedCardBrands' => $filteredCardBrands,
                'displayPending' => $displayPending,
            ];

            // Send a POST request to the server
            $response = $this->httpRequest(
                'POST',
                $requestEndpoint,
                $requestHeaders,
                $requestBody,
                'Failed to add the invoice'
            );

            if (empty($response)) {
                throw new Exception('Order details missing from the response');
            }

            return PaylinkInvoiceResponse::fromResponseData($response);
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /**
     * Retrieves invoice details
     *
     * @param string $transactionNo The transaction number of the invoice to retrieve.
     *
     * @return PaylinkInvoiceResponse Returns the invoice details.
     * @throws Exception If authentication fails or if there's an issue with retrieving the invoice.
     */
    public function getInvoice(string $transactionNo): PaylinkInvoiceResponse
    {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->authentication();
            }

            // Request Endpoint
            $requestEndpoint = "{$this->apiBaseUrl}/api/getInvoice/{$transactionNo}";

            // Request headers
            $requestHeaders = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->idToken}",
            ];

            // Send a GET request to the server
            $response = $this->httpRequest(
                'GET',
                $requestEndpoint,
                $requestHeaders,
                null,
                'Failed to retrieve the invoice'
            );

            if (empty($response)) {
                throw new Exception('Order details missing from the response');
            }

            return PaylinkInvoiceResponse::fromResponseData($response);
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /**
     * Cancels an existing invoice.
     *
     * @param string $transactionNo The transaction number of the invoice to cancel.
     * 
     * @return bool
     * 
     * @throws Exception If canceling the invoice fails.
     */
    public function cancelInvoice(string $transactionNo): bool
    {
        try {
            // Ensure authentication is done
            if (empty($this->idToken)) {
                $this->authentication();
            }

            // Request Endpoint
            $requestEndpoint = "{$this->apiBaseUrl}/api/cancelInvoice";

            // Request headers
            $requestHeaders = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->idToken}",
            ];

            // Request body parameters
            $requestBody = [
                'transactionNo' => $transactionNo,
            ];

            // Send a POST request to the server
            $response = $this->httpRequest(
                'POST',
                $requestEndpoint,
                $requestHeaders,
                $requestBody,
                'Failed to cancel the invoice'
            );

            return $response['success'] === 'true';
        } catch (Exception $e) {
            throw $e; // Re-throw the exception for higher-level handling
        }
    }

    /** --------------------------------------------- HELPERS --------------------------------------------- */
    /**
     * Handle errors in Paylink response.
     *
     * @param array $responseData The response data as an associative array.
     * @param int $statusCode The HTTP status code of the response.
     * @param string $defaultErrorMsg The default error message to use if no specific error is found.
     * @throws \Exception
     */
    private function handleResponseError(array $responseData, int $statusCode, string $defaultErrorMsg)
    {
        // Try to extract error details from the response data
        $errorMsg = $responseData['detail'] ?? $responseData['title'] ?? $responseData['error'] ?? '';

        if (empty($errorMsg)) {
            $errorMsg = $defaultErrorMsg;
        }

        // Include the status code in the error message for debugging purposes
        $errorMsg .= ", Status code: {$statusCode}";

        throw new Exception($errorMsg, $statusCode);
    }

    /**
     * Handles HTTP requests.
     *
     * @param string $method The HTTP method (GET or POST).
     * @param string $url The URL to send the request to.
     * @param array $headers The request headers.
     * @param array|null $body The request body, if applicable.
     * @param string $defaultErrorMsg The default error message to use if no specific error is found.
     * @return array The JSON-decoded response.
     * @throws Exception If the request fails.
     */
    private function httpRequest(string $method, string $url, array $headers, ?array $body = null, string $defaultErrorMsg): array
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, json_encode($headers));

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        } elseif ($method === 'GET') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        } else {
            throw new Exception("Invalid HTTP method: {$method}");
        }

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $this->handleResponseError(
                json_decode($response, true),
                curl_getinfo($curl, CURLINFO_HTTP_CODE),
                $defaultErrorMsg
            );
        }

        curl_close($curl);

        return json_decode($response, true);
    }

    public function getPaymentPageUrl(string $transactionNo): string
    {
        return "{$this->paymentBaseUrl}/{$transactionNo}";
    }
}
