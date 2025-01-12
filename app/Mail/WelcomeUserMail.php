<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable
{
    use SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   

        return $this->view('emails.welcome')
                    ->subject('Bem-vindo ao nosso sistema!')
                    ->with([
                        'name' => $this->user->name,
                    ]);
    }
}
