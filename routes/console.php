<?php

use App\Models\ConversionKey;
use App\Models\ConversionLog;
use App\Modules\Api\ClinikoApi;
use App\Modules\Api\GoHighLevelApi;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('test:log', function () {
    logger(Inspiring::quote());
})->purpose('Log an inspiring quote');

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

Artisan::command('test-code:api', function () {
    $cliniko = new ClinikoApi;
    $cliniko->setToken(env('CLINIKO_USER'));
    $appTypes = $cliniko->request('appointment_types');

    $ghl = new GoHighLevelApi;
    $ghl->setToken(env('GO_HIGH_LEVEL_KEY'));
    $pipelines = $ghl->request('pipelines');
})->purpose('Test code');

Artisan::command('test-code:logs', function () {
    $logs = ConversionLog::get();
    foreach ($logs as $log) {
        dump($log->toArray());
    }
})->purpose('Test code');
