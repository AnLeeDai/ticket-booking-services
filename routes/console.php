<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tự động huỷ vé IS_PENDING quá hạn - chạy mỗi phút
Schedule::command('tickets:cancel-expired')->everyMinute();
