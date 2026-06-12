<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invitation $invitation)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been invited to join '.$this->invitation->tenant->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invitation',
            with: [
                'organization' => $this->invitation->tenant->name,
                'role' => $this->invitation->role->label(),
                'acceptUrl' => route('invitations.show', ['token' => $this->invitation->token]),
            ],
        );
    }
}
