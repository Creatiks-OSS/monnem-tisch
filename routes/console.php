<?php

use App\Models\AdminNotification;
use App\Models\BlockedDate;
use App\Models\BlogPost;
use App\Models\Coupon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Coupon::query()
        ->where('is_active', true)
        ->whereNotNull('expires_at')
        ->where('expires_at', '<', now())
        ->update(['is_active' => false]);
})->daily()->name('expire-coupons');

Schedule::call(function () {
    BlogPost::query()
        ->where('is_published', false)
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->update(['is_published' => true]);
})->everyFifteenMinutes()->name('publish-scheduled-posts');

Schedule::call(function () {
    AdminNotification::query()
        ->whereNotNull('read_at')
        ->where('read_at', '<', now()->subDays(90))
        ->delete();
})->weekly()->name('cleanup-old-notifications');

Schedule::call(function () {
    BlockedDate::query()
        ->where('date', '<', now()->subYear()->toDateString())
        ->delete();
})->monthly()->name('cleanup-old-blocked-dates');
