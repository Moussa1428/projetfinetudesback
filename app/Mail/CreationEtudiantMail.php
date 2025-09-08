<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CreationEtudiantMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $password;
    public $name;

    public function __construct($name, $email, $password)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vos identifiants de connexion Etudiant',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.creation_etudiant',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
