import PublicLayout from '@/Components/Layout/PublicLayout';
import FlightSearchForm, { type FlightSearchDefaults } from '@/Components/Flights/FlightSearchForm';

const defaults: FlightSearchDefaults = {
  origin: 'DAC',
  destination: 'CXB',
  departure_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10),
  return_date: null,
  trip_type: 'one_way',
  adult_count: 1,
  child_count: 0,
  infant_count: 0,
  cabin_class: 'economy',
  currency: 'BDT',
  provider: 'duffel',
};

export default function Home() {
  return (
    <PublicLayout>
      <section className="bg-gradient-to-b from-white to-slate-100">
        <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-20">
          <div className="max-w-3xl">
            <p className="text-sm font-bold uppercase tracking-[0.3em] text-slate-500">Flight booking MVP</p>
            <h1 className="mt-4 text-4xl font-black tracking-tight text-slate-950 sm:text-6xl">
              Search, select, and prepare bookings without leaving the site.
            </h1>
            <p className="mt-5 text-lg leading-8 text-slate-600">
              Phase 9–10 adds server-side markup/promo pricing and a Reverb-ready support chat workflow.
            </p>
          </div>
          <div className="mt-10">
            <FlightSearchForm defaults={defaults} />
          </div>
        </div>
      </section>
    </PublicLayout>
  );
}
