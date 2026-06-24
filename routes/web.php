<?php

use App\Http\Controllers\Account\BookingHistoryController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ManageBooking\LookupController as ManageBookingLookupController;
use App\Http\Controllers\ManageBooking\ReceiptController as ManageBookingReceiptController;
use App\Http\Controllers\ManageBooking\ShowController as ManageBookingShowController;
use App\Http\Controllers\Flights\BookingConfirmationController;
use App\Http\Controllers\Flights\CheckoutController;
use App\Http\Controllers\Flights\FareDetailsController;
use App\Http\Controllers\Flights\FlightSearchController;
use App\Http\Controllers\Flights\OfferSelectionController;
use App\Http\Controllers\Flights\PassengerDetailsController;
use App\Http\Controllers\Payments\MockStripePaymentSuccessController;
use App\Http\Controllers\Payments\StripePaymentIntentController;
use App\Http\Controllers\Payments\StripeWebhookController;
use App\Http\Controllers\Pricing\ApplyPromoCodeController;
use App\Http\Controllers\Support\ListMessagesController;
use App\Http\Controllers\Support\SendMessageController;
use App\Http\Controllers\Support\StartConversationController;
use App\Http\Controllers\Security\HealthCheckController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home'))->name('home');
Route::get('/health/secure', HealthCheckController::class)->middleware('throttle:admin-sensitive')->name('health.secure');

Route::prefix('flights')->name('flights.')->group(function (): void {
    Route::get('/search', [FlightSearchController::class, 'create'])->name('search');
    Route::post('/search', [FlightSearchController::class, 'store'])->middleware('throttle:flight-search')->name('search.store');
    Route::get('/results/{flightSearch}', [FlightSearchController::class, 'results'])->name('results');
    Route::get('/offers/{flightOffer}', [FareDetailsController::class, 'show'])->name('offers.show');
    Route::post('/offers/{flightOffer}/select', [OfferSelectionController::class, 'store'])->middleware('throttle:checkout')->name('offers.select');
    Route::get('/bookings/{booking:booking_reference}/passengers', [PassengerDetailsController::class, 'edit'])->name('passengers');
    Route::post('/bookings/{booking:booking_reference}/passengers', [PassengerDetailsController::class, 'store'])->middleware('throttle:checkout')->name('passengers.store');
    Route::get('/bookings/{booking:booking_reference}/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/bookings/{booking:booking_reference}/promo-code', ApplyPromoCodeController::class)->middleware('throttle:checkout')->name('promo-code.apply');
    Route::post('/bookings/{booking:booking_reference}/payment-intent', StripePaymentIntentController::class)->middleware('throttle:payment')->name('payments.intent');
    Route::post('/bookings/{booking:booking_reference}/payments/{payment}/mock-succeed', MockStripePaymentSuccessController::class)->middleware('throttle:payment')->name('payments.mock-succeed');
    Route::get('/bookings/{booking:booking_reference}/confirmation', [BookingConfirmationController::class, 'show'])->name('confirmation');
});

Route::get('/account/login', [AuthenticatedSessionController::class, 'create'])->middleware('guest')->name('login');
Route::post('/account/login', [AuthenticatedSessionController::class, 'store'])->middleware(['guest', 'throttle:auth'])->name('login.store');
Route::post('/account/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
Route::get('/account/register', [RegisteredUserController::class, 'create'])->middleware('guest')->name('register');
Route::post('/account/register', [RegisteredUserController::class, 'store'])->middleware(['guest', 'throttle:auth'])->name('register.store');
Route::get('/account/bookings', BookingHistoryController::class)->middleware('auth')->name('account.bookings');

Route::prefix('manage-booking')->name('manage-booking.')->group(function (): void {
    Route::get('/', [ManageBookingLookupController::class, 'create'])->name('lookup');
    Route::post('/', [ManageBookingLookupController::class, 'store'])->middleware('throttle:manage-booking')->name('lookup.store');
    Route::get('/{booking:booking_reference}', ManageBookingShowController::class)->name('show');
    Route::get('/{booking:booking_reference}/receipt', ManageBookingReceiptController::class)->name('receipt');
});

Route::prefix('support/chat')->name('support.chat.')->middleware('throttle:chat')->group(function (): void {
    Route::post('/start', StartConversationController::class)->name('start');
    Route::get('/conversations/{conversation}/messages', ListMessagesController::class)->name('messages');
    Route::post('/conversations/{conversation}/messages', SendMessageController::class)->name('messages.store');
});

Route::post('/webhooks/stripe', StripeWebhookController::class)->middleware('throttle:payment-webhook')->name('webhooks.stripe');
