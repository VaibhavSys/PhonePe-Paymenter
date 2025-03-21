<?php

namespace Paymenter\Extensions\Gateways\PhonePe;

use App\Classes\Extension\Gateway;
use Illuminate\Support\Facades\Http;
use App\Helpers\ExtensionHelper;
use Illuminate\Http\Request;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class PhonePe extends Gateway
{

    public function boot()
    {
        require __DIR__ . '/routes.php';
    }

    /**
     * Get all the configuration for the extension
     * 
     * @param array $values
     * @return array
     */
    public function getConfig($values = [])
    {
        return [
            [
                'name' => 'mid',
                'label' => 'Merchant ID',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'salt_key',
                'label' => 'Salt Key',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'salt_index',
                'label' => 'Salt Index',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'order_prefix',
                'label' => 'Order Prefix',
                'type' => 'text',
                'required' => false,
            ],
            [
                'name' => 'live',
                'label' => 'Live Mode',
                'type' => 'checkbox',
                'required' => false,
            ],
            [
                'name' => 'allow_foreign_currency',
                'label' => 'Allow Foreign Currency',
                'type' => 'checkbox',
            ]
        ];
    }
    
    /**
     * Return a view or a url to redirect to
     * 
     * @param Invoice $invoice
     * @param float $total
     * @return string
     */
    public function pay($invoice, $total)
    {
        $mid = $this->config['mid'];
        $saltKey = $this->config['salt_key'];
        $saltIndex = $this->config['salt_index'];
        $orderPrefix = $this->config['order_prefix'];
        $orderId = $orderPrefix . $invoice->id;
        $amount = $total * 100;
        $redirect = route('invoices.show', ['invoice' => $invoice->id]);
        $callback = route('extensions.gateways.phonepe.webhook');
        $url = $this->getEndpoint();
        $allowForeignCurrency = $this->config('allow_foreign_currency');

        if (!$allowForeignCurrency && $invoice->currency_code != 'INR') {
            Log::info('Foreign currency not allowed');
            return redirect()->route('invoices.show', ['invoice' => $invoice->id])->with('notification', [
                'message' => 'Foreign currency not allowed',
                'type' => 'error',
            ]);
        }

        if ($amount <= 0) {
            return redirect()->route('invoices.show', ['invoice' => $invoice->id])->with('notification', [
                'type' => 'error',
                'message' => 'Invalid amount',
            ]);
        }

        $data = [
            'merchantId' => $mid,
            'merchantTransactionId' => $orderId,
            'amount' => $amount,
            'merchantUserId' => $invoice->user_id,
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
            return redirect()->route('clients.invoice.show', $invoice->id)->with('notification', [
                'type' => 'error',
                'message' => 'Payment failed',
            ]);
        }
        
        $paymentUrl = $responseJson['data']['instrumentResponse']['redirectInfo']['url'];

        return $paymentUrl;
    }

    public function getEndpoint() {
        $live = $this->config('live');
        return $live ? 'https://api.phonepe.com/apis/hermes/pg/v1/pay' : 'https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay';
    }

    public function extractInvoiceId($orderId)
    {
        $orderPrefix = $this->config('order_prefix');
        $invoiceId = (int) substr($orderId, strlen($orderPrefix));
        return $invoiceId;
    }

    public function webhook(Request $request) {
        $saltKey = $this->config('salt_key');
        $saltIndex = $this->config('salt_index');
        $posted = $request->all();
        $response = $posted['response'];
        $checksum = $request->header('X-VERIFY');
        $expectedChecksum = hash('sha256', $response . $saltKey) . '###' . $saltIndex;
        if ($checksum !== $expectedChecksum) {
            throw new \Exception('PhonePe: Checksum mismatch: Posted: ' . json_encode($posted) . ' ' . $checksum . ' !== ' . $expectedChecksum);
            return;
        }

        $payload = json_decode(base64_decode($response), true);
        if ($payload['code'] !== 'PAYMENT_SUCCESS') {
            throw new \Exception('PhonePe: Payment Failed: ' . json_encode($payload));
            return;
        }

        $orderId = $payload['data']['merchantTransactionId'];
        $invoiceId = $this->extractInvoiceId($orderId);
        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            throw new \Exception('PhonePe: Invoice not found: ' . $invoiceId);
            return;
        }

        $amountPaid = $payload['data']['amount'] / 100;

        ExtensionHelper::addPayment($invoiceId, 'PhonePe', $amountPaid, null, $orderId);
    }
}
