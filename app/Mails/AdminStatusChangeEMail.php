<?php

namespace App\Mails;

use App\Models\User;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminStatusChangeEMail extends Mailable
{
    use Queueable, SerializesModels;

    public $adminId, $childId, $status;

    public function __construct($adminId, $childId, $status)
    {
        $this->adminId = $adminId;
        $this->childId = $childId;
        $this->status = $status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $admin = User::find($this->adminId);
        if (! $admin) return false;

        $child = User::find($this->childId);
        if (! $child) return false;

        $subject = $this->status ? 'Admin access Granted' : 'Admin access Revoked';
        $url = env('FRONT_END_URL') . "/admin/";
        \Log::info(env('MAIL_ADMIN_FROM_ADDRESS'));
        \Log::info(env('MAIL_FROM_NAME'));
        return $this->from(env('MAIL_ADMIN_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->subject($subject)
                ->with([
                    'name' => $child->first_name,
                    'admin_name' => $admin->first_name,
                    'admin_email' => $admin->email,
                    'status' => $this->status,
                    'url' => $url
                ])
                ->view('emails.admin.access-status-change');
    }
}