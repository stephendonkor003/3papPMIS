<?php

namespace App\Mail;

use App\Models\Funder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FundingPartnerWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public $funder;
    public $user;
    public $plainPassword;

    /**
     * Create a new message instance.
     */
    public function __construct(Funder $funder, User $user, string $plainPassword)
    {
        $this->funder = $funder;
        $this->user = $user;
        $this->plainPassword = $plainPassword;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject(__('partner.email.welcome_subject'))
            ->view('emails.partners.welcome')
            ->with([
                'funder'        => $this->funder,
                'user'          => $this->user,
                'plainPassword' => $this->plainPassword,
                'loginUrl'      => route('partner.dashboard'),
            ]);
    }
}
