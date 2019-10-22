<?php

namespace App\Mails;

use App\Models\{Service, User};

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ServiceApprovalEMail extends Mailable
{
    use Queueable, SerializesModels;

    public $serviceId;

    public function __construct($serviceId)
    {
        $this->serviceId = $serviceId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $service = Service::find($this->serviceId);
        if (! $service) return false;
     
        $provider = $service->user;
        if (! $provider) return false;

        $subject = 'Service approved';
        $url = env('FRONT_END_URL') . "/marketplace/" . $service->category->slug . "/" . $service->subCategory->slug . "/" . $service->id;

        return $this->from(env('MAIL_ADMIN_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->subject($subject)
                ->with([
                    'name' => $provider->first_name,
                    'service_title' => $service->title,
                    'service_url' => $url,
                ])
                ->view('emails.service.approved');
    }
}