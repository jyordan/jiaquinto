<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('test-code:mail', function () {
    Mail::raw('This is a test email.', function ($message) {
        $message->to(env('DEV_EMAIL'))->subject('Test Email');
    });
})->purpose('Test email');
