<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('test-code:mail {email?}', function () {
    $email = $this->argument('email');
    Mail::raw('This is a test email.', function ($message) use ($email) {
        $message->to($email ?: env('DEV_EMAIL'))->subject('Test Email');
    });
})->purpose('Test email');
