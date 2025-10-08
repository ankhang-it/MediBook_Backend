<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create payment for appointment
     */
    public function createPayment(Request $request)
    {
        try {
            $request->validate([
                'appointment_id' => 'required|string|exists:appointments,appointment_id'
            ]);

            $transaction = $this->paymentService->createPaymentTransaction($request->appointment_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->id,
                    'checkout_url' => $transaction->checkout_url,
                    'qr_code' => $transaction->qr_code,
                    'amount' => $transaction->amount
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating payment', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment callback from PayOS
     */
    public function callback(Request $request)
    {
        Log::info('Payment callback received', [
            'method' => $request->method(),
            'data' => $request->all()
        ]);

        if ($request->isMethod('get')) {
            try {
                $paymentLinkId = $request->query('id');
                $code = $request->query('code');
                $status = $request->query('status');

                if ($paymentLinkId && $code === '00' && $status === 'PAID') {
                    $callbackData = [
                        'data' => [
                            'paymentLinkId' => $paymentLinkId,
                            'code' => $code,
                            'amount' => $request->query('amount', 0),
                            'transactionDateTime' => now()->toIso8601String(),
                        ],
                    ];

                    $result = $this->paymentService->handlePaymentCallback($callbackData);
                    Log::info('GET callback processed', ['result' => $result]);

                    return redirect(config('payos.return_url'))->with('success', 'Payment successful!');
                }

                Log::warning('Invalid GET callback data', ['data' => $request->all()]);
                return redirect(config('payos.cancel_url'))->with('error', 'Invalid payment callback');
            } catch (\Exception $e) {
                Log::error('Error processing GET callback', ['error' => $e->getMessage()]);
                return redirect(config('payos.cancel_url'))->with('error', 'Payment processing error');
            }
        }

        try {
            $result = $this->paymentService->handlePaymentCallback($request->all());
            Log::info('POST callback processed', ['result' => $result]);
            return response()->json(['message' => 'Payment processed']);
        } catch (\Exception $e) {
            Log::error('Error processing POST callback', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Payment processing failed'], 500);
        }
        //return response()->json('Payment callback received');
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(Request $request)
    {
        try {
            $request->validate([
                'payment_id' => 'required|string|exists:payments,payment_id'
            ]);

            $payment = \App\Models\Payment::find($request->payment_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_id' => $payment->payment_id,
                    'is_paid' => $payment->isPaid(),
                    'paid_at' => $payment->paid_at,
                    'payment_status' => $payment->payment_status,
                    'amount' => $payment->total_amount,
                    'status' => $payment->status,
                    'checkout_url' => $payment->checkout_url
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting payment status', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment status: ' . $e->getMessage()
            ], 500);
        }
    }
}
