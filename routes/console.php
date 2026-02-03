<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule payment due reminders to run daily
Schedule::command('payments:send-due-reminders')
    ->daily()
    ->at('09:00')
    ->description('Send payment due reminder emails to exhibitors 1 day before payment due date');

// Schedule 3-day-before part payment reminders (BCC asadm@alakmalak.com for testing)
Schedule::command('payments:send-3day-reminders')
    ->daily()
    ->at('09:00')
    ->description('Send payment due reminder emails 3 days before part payment due date');
