<?php

namespace App\Mail;

use App\Models\HelpTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HelpTicketReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public HelpTicket $ticket;

    public function __construct(HelpTicket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function build()
    {
        return $this
            ->subject('Reply to your Help Ticket #' . $this->ticket->id . ' - ' . $this->ticket->subject)
            ->view('emails.help-ticket-reply');
    }
}
