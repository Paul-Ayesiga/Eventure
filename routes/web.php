<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\EventController;

Route::get('/', function () {
    return view('welcome');
})->name('home');




// admin routes
Route::prefix('admin')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::redirect('settings', 'settings-admin/profile');

    Route::view('settings/profile', 'admin.settings.profile')->name('admin.settings.profile');
    Route::view('settings/password', 'admin.settings.password')->name('admin.settings.password');
    Route::view('settings/appearance', 'admin.settings.appearance')->name('admin.settings.appearance');

    // Platform routes
    Route::view('dashboard', 'admin.dashboard')->name('admin-dashboard');
});


// event organiser routes
Route::prefix('org')->middleware(['auth', 'verified', 'role:organiser'])->group(function () {
    Route::redirect('settings', 'settings-org/profile');

    Route::view('settings/profile', 'organiser.settings.profile')->name('org.settings.profile');
    Route::view('settings/password', 'organiser.settings.password')->name('org.settings.password');
    Route::view('settings/appearance', 'organiser.settings.appearance')->name('org.settings.appearance');

    // Platform routes
    Route::view('dashboard', 'organiser.dashboard')->name('organiser-dashboard');
    Route::view('events', 'organiser.events')->name('events');
    Route::view('reports', 'organiser.reports')->name('reports');
    Route::view('my-team', 'organiser.my-team')->name('my-team');
    Route::view('contacts', 'organiser.contacts')->name('contacts');
    Route::view('organisation-profile', 'organiser.organisation-profile')->name('organisation-profile');
    Route::view('coupons', 'organiser.coupons')->name('coupons');
    Route::view('tracking-codes', 'organiser.tracking-codes')->name('tracking-codes');
    Route::view('payment-collections', 'organiser.payment-collections')->name('payment-collections');
    Route::view('billing-details', 'organiser.billing-details')->name('billing-details');
    Route::view('subscription', 'organiser.subscription')->name('subscription');
    Route::get('merchandise', fn() => null)->name('merchandise');
    Route::get('help-support', fn() => null)->name('help-support');

    // Event routes
    Route::view('event/{id}/details', 'organiser.event-details')->name('event-details');
    Route::view('event/{id}/tickets', 'organiser.event-tickets')->name('event-tickets');
    Route::view('event/{id}/bookings', 'organiser.event-bookings')->name('event-bookings');
    Route::view('event/{id}/insights', 'organiser.event-insights')->name('event-insights');
    Route::view('event/{id}/settings', 'organiser.event-settings')->name('event-settings');
});



// event details routes for organiser to manage events
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/events/{id}/details', \App\Livewire\Org\Events\EventDetails::class)->name('event-details');
    Route::get('/events/{id}/tickets', \App\Livewire\Org\Events\Tickets::class)->name('event-tickets');
    Route::get('/events/{id}/bookings', \App\Livewire\Org\Events\Bookings::class)->name('event-bookings');
    Route::get('/events/{id}/insights', \App\Livewire\Org\Events\Insights::class)->name('event-insights');
    Route::get('/events/{id}/settings', \App\Livewire\Org\Events\EventSettings::class)->name('event-settings');
});


// user routes
Route::prefix('usr')->middleware(['auth', 'verified', 'role:user'])->group(function () {
    Route::redirect('settings', 'settings-usr/profile');

    Route::view('settings/profile', 'user.settings.profile')->name('usr.settings.profile');
    Route::view('settings/password', 'user.settings.password')->name('usr.settings.password');
    Route::view('settings/appearance', 'user.settings.appearance')->name('usr.settings.appearance');

    // Platform routes
    Route::view('dashboard', 'user.dashboard')->name('user-dashboard');
});


// Event Tickets Route


require __DIR__ . '/auth.php';
