<?php

namespace App\Mails;

use App\Models\User;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyEMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = User::find($this->userId);
        if (! $user) return false;

        $subject = 'CoSound: Please verify your e-mail address';
        $name = $user->first_name;
        $email = $user->email;
        $confirmationCode = $user->confirmation_code;

        return $this->from(env('MAIL_VERIFY_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->subject($subject)
                ->with([
                    'name' => $name,
                    'confirmation_code' => $confirmationCode
                ])
                ->view('emails.verify');
    }
}
