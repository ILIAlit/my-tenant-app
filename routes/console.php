<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:create-charges')->daily();
Schedule::command('app:send-charge-payment-reminders')->daily();
Schedule::command('app:mark-overdue-charges-as-debt')->daily();
