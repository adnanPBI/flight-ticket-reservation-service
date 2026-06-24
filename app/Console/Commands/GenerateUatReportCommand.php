<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateUatReportCommand extends Command
{
    protected $signature = 'ota:uat-report {--output=storage/app/uat-report.md}';

    protected $description = 'Generate a markdown UAT report template for client sign-off.';

    public function handle(): int
    {
        $path = base_path($this->option('output'));
        File::ensureDirectoryExists(dirname($path));

        $content = <<<'MD'
# OTA Flight Booking UAT Report

## Environment
- URL:
- Date:
- Tester:
- Build/version:
- Browser/device:

## Critical path results

| Area | Scenario | Result | Notes |
|---|---|---:|---|
| Search | One-way flight search returns results | ☐ Pass ☐ Fail | |
| Search | Round-trip search returns results | ☐ Pass ☐ Fail | |
| Offer | Expired offer cannot proceed | ☐ Pass ☐ Fail | |
| Passenger | Passenger count/type validation works | ☐ Pass ☐ Fail | |
| Payment | Mock Stripe payment succeeds | ☐ Pass ☐ Fail | |
| Booking | Provider finalization job creates mock PNR | ☐ Pass ☐ Fail | |
| Admin | Booking visible in Filament | ☐ Pass ☐ Fail | |
| Admin | Failed booking visible in queue | ☐ Pass ☐ Fail | |
| Chat | Customer sends message | ☐ Pass ☐ Fail | |
| Chat | Admin replies from Filament | ☐ Pass ☐ Fail | |
| Manage booking | Guest lookup by reference/email works | ☐ Pass ☐ Fail | |
| Security | Other user cannot view private booking | ☐ Pass ☐ Fail | |

## Open defects

| Severity | Description | Owner | Status |
|---|---|---|---|
| | | | |

## Sign-off

Client representative:

Date:

Notes:
MD;

        File::put($path, $content);
        $this->info("UAT report template created at {$path}");

        return self::SUCCESS;
    }
}
