<?php

namespace App\Mail;

use App\Models\Election;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VoteConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $election;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Election $election)
    {
        $this->user = $user;
        $this->election = $election;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vote Confirmation: ' . $this->election->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.vote_confirmation', // We will need to create this view!
            with: [
                'name' => $this->user->name,
                'electionTitle' => $this->election->title,
                'time' => now()->format('F j, Y, g:i a'),
            ],
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
