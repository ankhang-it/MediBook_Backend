<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Appointment;
use App\Models\TimeSlot;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PayOS\PayOS;

class PaymentService
{
    protected $payOS;

    public function __construct()
    {
        $this->payOS = new PayOS(
            config('payos.client_id'),
            config('payos.api_key'),
            config('payos.checksum_key')
        );

        Log::info('PayOS Service initialized', [
            'client_id_exists' => !empty(config('payos.client_id')),
            'api_key_exists' => !empty(config('payos.api_key')),
            'checksum_key_exists' => !empty(config('payos.checksum_key'))
        ]);
    }

    /**
     * Generate a random order code that meets PayOS requirements
     */
    private function generateOrderCode(): int
    {
        return random_int(1, 999999);
    }

    /**
     * Create a payment transaction for the given appointment ID.
     */
    public function createPaymentTransaction($appointmentId)
    {
        try {
            Log::info('Starting payment transaction creation', ['appointment_id' => $appointmentId]);

            $appointment = Appointment::with(['patient.user', 'doctor.user', 'timeSlot'])->find($appointmentId);
            if (!$appointment) {
                Log::error('Appointment not found', ['appointment_id' => $appointmentId]);
                throw new \Exception('Appointment not found');
            }

            Log::info('Appointment found for payment', [
                'appointment_id' => $appointmentId,
                'consultation_fee' => $appointment->doctor->consultation_fee
            ]);

            $existingPayment = Payment::where('appointment_id', $appointmentId)->first();
            if ($existingPayment) {
                Log::info('Existing payment found', [
                    'payment_id' => $existingPayment->payment_id,
                    'checkout_url' => $existingPayment->checkout_url
                ]);
                return $existingPayment;
            }

            $orderCode = $this->generateOrderCode();
            $amount = $appointment->doctor->consultation_fee ?? 500000; // Default consultation fee

            $payload = [
                'orderCode' => $orderCode,
                'amount' => (int)$amount,
                'description' => 'Thanh toan lich kham',
                'returnUrl' => config('payos.return_url'),
                'cancelUrl' => config('payos.cancel_url'),
            ];

            $response = $this->payOS->createPaymentLink($payload);
            Log::info('PayOS payment response', ['response' => $response]);

            $paymentData = [
                'payment_id' => \Illuminate\Support\Str::uuid(),
                'appointment_id' => $appointment->appointment_id,
                'order_code' => $orderCode,
                'total_amount' => $amount,
                'description' => $payload['description'],
                'status' => 'pending',
                'user_id' => $appointment->patient->user_id,
                'payment_link_id' => $response['paymentLinkId'],
                'checkout_url' => $response['checkoutUrl'],
                'qr_code' => $response['qrCode'] ?? null,
                'payment_info' => [
                    'payment_type' => null,
                    'status' => 'pending'
                ]
            ];

            $payment = Payment::create($paymentData);
            Log::info('Payment created successfully', [
                'payment_id' => $payment->payment_id,
                'checkout_url' => $payment->checkout_url
            ]);

            return $payment;
        } catch (\Exception $e) {
            Log::error('Error in createPaymentTransaction', [
                'appointment_id' => $appointmentId,
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Find a transaction by payment link ID.
     */
    public function findPaymentByPaymentLinkId($paymentLinkId)
    {
        return Payment::where('payment_link_id', $paymentLinkId)->first();
    }

    /**
     * Handle the payment callback from PayOS.
     */
    public function handlePaymentCallback($data)
    {
        Log::info('Received PayOS callback request', [
            'headers' => request()->headers->all(),
            'data' => $data
        ]);

        DB::beginTransaction();
        try {
            Log::info('Processing payment callback', ['data' => $data]);
            if (!isset($data['data']['paymentLinkId']) || !isset($data['data']['code'])) {
                Log::error('Invalid callback data', ['data' => $data]);
                throw new \Exception('Invalid callback data');
            }

            $paymentData = $data['data'];
            $payment = Payment::where('payment_link_id', $paymentData['paymentLinkId'])->first();
            if (!$payment) {
                Log::error('Payment not found', ['payment_link_id' => $paymentData['paymentLinkId']]);
                throw new \Exception('Payment not found');
            }

            $updateData = [
                'payment_info' => array_merge($payment->payment_info ?? [], [
                    'status' => $paymentData['code'],
                    'payment_method' => $paymentData['counterAccountBankName'] ?? null,
                    'payment_time' => $paymentData['transactionDateTime'] ?? now()->toIso8601String(),
                ]),
                'paid_at' => $paymentData['code'] === '00' ? now() : null,
                'status' => $paymentData['code'] === '00' ? 'completed' : 'failed',
            ];

            $payment->update($updateData);
            Log::info('Payment updated', ['payment_id' => $payment->payment_id]);

            $appointment = Appointment::find($payment->appointment_id);
            if (!$appointment) {
                Log::error('Appointment not found', ['appointment_id' => $payment->appointment_id]);
                throw new \Exception('Appointment not found');
            }

            if ($paymentData['code'] === '00') {
                // Update appointment status to confirmed
                $appointment->update([
                    'status' => 'confirmed',
                    'payment_status' => 'paid'
                ]);

                // Mark time slot as unavailable
                if ($appointment->timeSlot) {
                    $appointment->timeSlot->update(['is_available' => false]);
                }

                Log::info('Appointment confirmed and time slot marked as unavailable', [
                    'appointment_id' => $appointment->appointment_id,
                    'time_slot_id' => $appointment->time_slot_id
                ]);

                // Send email notifications
                try {
                    // Load relationships for email
                    $appointment->load(['patient.user', 'doctor.user', 'doctor.specialty']);

                    // Send email to patient
                    if ($appointment->patient && $appointment->patient->user && $appointment->patient->user->email) {
                        \Mail::to($appointment->patient->user->email)
                            ->send(new \App\Mail\AppointmentConfirmedPatient($appointment));
                        Log::info('Confirmation email sent to patient', [
                            'patient_email' => $appointment->patient->user->email
                        ]);
                    }

                    // Send email to doctor
                    if ($appointment->doctor && $appointment->doctor->user && $appointment->doctor->user->email) {
                        \Mail::to($appointment->doctor->user->email)
                            ->send(new \App\Mail\AppointmentConfirmedDoctor($appointment));
                        Log::info('Notification email sent to doctor', [
                            'doctor_email' => $appointment->doctor->user->email
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log email error but don't fail the transaction
                    Log::error('Failed to send appointment confirmation emails', [
                        'error' => $e->getMessage(),
                        'appointment_id' => $appointment->appointment_id
                    ]);
                }
            }

            DB::commit();
            return ['message' => 'Payment processed successfully'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment callback failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
