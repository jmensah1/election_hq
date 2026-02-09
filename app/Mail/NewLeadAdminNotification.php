<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewLeadAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    public function build()
    {
        return $this->subject('New Elections HQ Application - ' . $this->lead->organization_name . ' (' . ucfirst($this->lead->plan_tier) . ')')
                    ->markdown('emails.leads.admin');
    }
}
