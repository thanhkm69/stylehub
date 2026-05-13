<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AdminReplyMail extends Mailable
{
    public Contact $contact;
    public string $replyNote;

    /**
     * Create a new message instance.
     */
    public function __construct(Contact $contact, string $replyNote)
    {
        $this->contact = $contact;
        $this->replyNote = $replyNote;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Trả lời liên hệ từ StyleHub',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-reply',
            with: [
                'contact' => $this->contact,
                'replyNote' => $this->replyNote,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
