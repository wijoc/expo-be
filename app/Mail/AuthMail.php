<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class AuthMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $body;

    public function __construct(Array $data)
    {
        $this->body = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.otp_registration', [
            'otp' => $this->body['otp'],
            'valid_until' => Carbon::parse($this->body['valid_until'], $this->body['valid_tz'])->setTimezone('UTC')->format('Y M d H:i:s'),
            'valid_tz' => $this->body['valid_tz']
        ]);
    }
}
