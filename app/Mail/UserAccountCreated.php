<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $plainPassword;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $plainPassword)
    {
        $this->user = $user;
        $this->plainPassword = $plainPassword;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Africa Think Tank Platform Account')
                    ->markdown('emails.users.created')
                    ->with([
                        'user' => $this->user,
                        'plainPassword' => $this->plainPassword,
                    ]);
    }
}
