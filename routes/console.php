<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Bulk discount campaigns are time ranged down to the minute, so the sync
 * runs every minute rather than nightly.
 */
Schedule::command('bulk-discount:sync')->everyMinute()->withoutOverlapping();

/*
 * Courier status only moves a few times a day per parcel, so a 20 minute
 * poll is enough. withoutOverlapping stops a slow Steadfast reply stacking
 * runs on top of each other.
 */
Schedule::command('steadfast:sync')->cron('*/20 * * * *')->withoutOverlapping();
