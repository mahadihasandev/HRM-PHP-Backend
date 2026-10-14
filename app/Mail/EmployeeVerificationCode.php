<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;

class EmployeeVerificationCode extends Mailable
{
    public function __construct(public readonly string $code) {}

    public function build(): self
    {
        return $this->subject('HRM - Verify your employee email')
            ->html('<h2>Verify your email for HRM</h2><p>Your employee registration code is:</p><p style="font-size:28px;letter-spacing:6px;font-weight:bold">'.$this->code.'</p><p>This code expires in 15 minutes. Share it only with the HR administrator registering your employee profile. If you did not expect this request, ignore this email.</p>');
    }
}
