<?php

use App\Jobs\ClearOldFlightOfferPayloadsJob;
use App\Jobs\DeleteAbandonedPaymentSessionsJob;
use App\Jobs\ExpireOldSearchSessionsJob;
use App\Jobs\SendPendingTicketingAlertJob;
use App\Jobs\SyncProviderBookingStatusJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new ExpireOldSearchSessionsJob)->everyFifteenMinutes();
Schedule::job(new ClearOldFlightOfferPayloadsJob)->dailyAt('02:00');
Schedule::job(new DeleteAbandonedPaymentSessionsJob)->everyThirtyMinutes();
Schedule::job(new SyncProviderBookingStatusJob)->everyFifteenMinutes();
Schedule::job(new SendPendingTicketingAlertJob)->hourly();

Schedule::command('ota:purge-expired-pii')->dailyAt('03:00');
