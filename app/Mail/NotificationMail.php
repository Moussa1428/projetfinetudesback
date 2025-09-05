<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $notification;
    /**
     * Create a new message instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle Notification',
        );
    }
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
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
