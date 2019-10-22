<?php

namespace App\Mails;

use App\Models\User;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $userId;
    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($userId, $token)
    {
        $this->userId = $userId;
        $this->token = $token;
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

        $subject = 'CoSound: Reset Password';
        $name = $user->first_name;
        $email = $user->email;
        $token = $this->token;

        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->subject($subject)
                ->with([
                    'name' => $name,
                    'token' => $token
                ])
                ->view('emails.reset-password');
    }
}
