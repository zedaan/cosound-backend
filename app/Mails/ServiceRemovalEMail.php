<?php

namespace App\Mails;

use App\Models\{Service, User};

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ServiceRemovalEMail extends Mailable
{
    use Queueable, SerializesModels;

    public $serviceTitle, $providerName, $comment;

    public function __construct($serviceTitle, $providerName, $comment = "")
    {
        $this->serviceTitle = $serviceTitle;
        $this->providerName = $providerName;
        $this->comment = $comment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Service removed';
        $url = env('FRONT_END_URL') . "/create-service";

        return $this->from(env('MAIL_ADMIN_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->subject($subject)
                ->with([
                    'name' => $this->providerName,
                    'service_title' => $this->serviceTitle,
                    'comment' => $this->comment,
                    'url' => $url,
                ])
                ->view('emails.service.removed');
    }
}