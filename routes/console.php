<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('test-code:mail {email?}', function () {
    $faker = Faker\Factory::create();

    $email = $this->argument('email');
    $content = $faker->paragraph();

    $subject = 'Test Email - ';
    $subject .= $faker->word();

    Mail::raw($content, function ($message) use ($email, $subject) {
        $message->to($email ?: env('DEV_EMAIL'))->subject($subject);
    });
})->purpose('Test email');
