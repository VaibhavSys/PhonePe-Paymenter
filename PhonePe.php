<?php

namespace App\Extensions\Gateways\PhonePe;

use App\Classes\Extensions\Gateway;
use App\Helpers\ExtensionHelper;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class PhonePe extends Gateway {
    /**
    * Get the extension metadata
    * 
    * @return array
    */
    public function getMetadata() {
        return [
            'display_name' => 'PhonePe',
            'version' => '1.0.1',
            'author' => 'Vaibhav',
            'website' => 'https://github.com/VaibhavSys',
        ];
    }

    /**
     * Get all the configuration for the extension
     * 
     * @return array
     */
    public function getConfig(){
        return [
            [
                'name' => 'mid',
                'type' => 'text',
                'friendlyName' => 'Merchant ID',
                'required' => true,
            ],
            [
                'name' => 'salt_key',
                'type' => 'text',
                'friendlyName' => 'Salt Key',
                'required' => true,
            ],
            [
                'name' => 'salt_index',
                'type' => 'text',
                'friendlyName' => 'Salt Index',
                'required' => true,
            ],
            [
                'name' => 'order_prefix',
                'type' => 'text',
                'friendlyName' => 'Order Prefix',
                'required' => false,
            ],
            [
                'name' => 'live',
                'type' => 'boolean',
                'friendlyName' => 'Live Mode',
                'required' => false,
            ]
        ];
    }
    
    /**
     * Get the URL to redirect to
     * 
     * @param int $total
     * @param array $products
     * @param int $invoiceId
     * @return string
     */
    public function pay($total, $products, $invoiceId) {
        $mid = ExtensionHelper::getConfig('PhonePe', 'mid');
        $saltKey = ExtensionHelper::getConfig('PhonePe', 'salt_key');
        $saltIndex = ExtensionHelper::getConfig('PhonePe', 'salt_index');
        $orderPrefix = ExtensionHelper::getConfig('PhonePe', 'order_prefix');
        $url = $this->getEndpoint();
        $orderId = $orderPrefix . $invoiceId;
        $invoice = Invoice::find($invoiceId);
        $amount = $total * 100; // Paise
        $redirect = route('clients.invoice.show', $invoiceId);
        $callback = route('phonepe.webhook');
        $data = [
            'merchantId' => $mid,
            'merchantTransactionId' => $orderId,
            'amount' => $amount,
            'merchantUserId' => $invoice,
            'redirectUrl' => $redirect,
            'redirectMode' => 'REDIRECT',
            'callbackUrl' => $callback,
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];
        $encodedData = base64_encode(json_encode($data));
        $checksum = hash('sha256', $encodedData . '/pg/v1/pay' . $saltKey) . '###' . $saltIndex;
        $payload = [
            'request' => $encodedData,
        ];
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-VERIFY' => $checksum,
        ])->post($url, $payload);
        $responseJson = $response->json();
        if ($response->failed() || $responseJson['success'] !== true) {
            ExtensionHelper::error('PhonePe', 'Payment Failed for invoice ' . $invoiceId, $responseJson);
            return redirect()->route('clients.invoice.show', $invoiceId)->with('error', 'Payment failed');
        }
        
        $paymentUrl = $responseJson['data']['instrumentResponse']['redirectInfo']['url'];

        return $paymentUrl;
    }

    public function getEndpoint() {
        $live = ExtensionHelper::getConfig('PhonePe', 'live');
        return $live ? 'https://api.phonepe.com/apis/hermes/pg/v1/pay' : 'https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay';
    }

    public static function extractInvoiceId($orderId)
    {
        $orderPrefix = ExtensionHelper::getConfig('PhonePe', 'order_prefix');
        $invoiceId = (int) substr($orderId, strlen($orderPrefix));
        return $invoiceId;
    }

    public function webhook(Request $request) {
        $saltKey = ExtensionHelper::getConfig('PhonePe', 'salt_key');
        $saltIndex = ExtensionHelper::getConfig('PhonePe', 'salt_index');
        $posted = $request->all();
        $response = $posted['response'];
        $checksum = $request->header('X-VERIFY');
        $expectedChecksum = hash('sha256', $response . $saltKey) . '###' . $saltIndex;
        if ($checksum !== $expectedChecksum) {
            ExtensionHelper::error('PhonePe', 'Checksum mismatch', ['response' => $response, 'checksum' => $checksum, 'expected' => $expectedChecksum]);
            return;
        }

        $payload = json_decode(base64_decode($response), true);
        if ($payload['success'] !== true) {
            ExtensionHelper::error('PhonePe', 'Payment failed', $payload);
            return;
        }

        $orderId = $payload['data']['merchantTransactionId'];
        $invoiceId = $this->extractInvoiceId($orderId);
        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            ExtensionHelper::error('PhonePe', 'Invoice not found', ['invoiceId' => $invoiceId]);
            return;
        }
        ExtensionHelper::paymentDone($invoiceId, 'PhonePe', $orderId);
    }
}
