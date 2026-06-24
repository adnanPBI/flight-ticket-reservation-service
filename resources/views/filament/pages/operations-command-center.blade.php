<x-filament-panels::page>
    <div class="space-y-4">
        <x-filament::section>
            <x-slot name="heading">OTA operations checklist</x-slot>
            <ul class="list-disc space-y-2 ps-6 text-sm text-gray-600 dark:text-gray-300">
                <li>Review paid bookings that are not confirmed yet.</li>
                <li>Retry provider finalization only after checking provider logs and duplicate order risk.</li>
                <li>Move booking to refund pending when payment succeeded but provider finalization cannot be recovered.</li>
                <li>Use provider logs for request/response audit before contacting Duffel/Amadeus support.</li>
            </ul>
        </x-filament::section>
    </div>
</x-filament-panels::page>
