<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmedPatient extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;
    public $patient;
    public $doctor;
    public $doctorProfile;
    public $payment;

    /**
     * Create a new message instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
        $this->patient = $appointment->patient->user;
        $this->doctor = $appointment->doctor->user; // Load doctor's user
        $this->doctorProfile = $appointment->doctor; // Keep doctor profile for specialty
        $this->payment = $appointment->payment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Xác nhận đặt lịch khám bệnh thành công - MediBook',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-confirmed-patient',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
