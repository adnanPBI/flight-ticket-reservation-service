import PublicLayout from '@/Components/Layout/PublicLayout';
import FlightResultCard, { type FlightOffer } from '@/Components/Flights/FlightResultCard';

export type SearchSummary = {
  id: number;
  reference: string;
  origin: string;
  destination: string;
  departure_date?: string | null;
  return_date?: string | null;
  trip_type: string;
  adult_count: number;
  child_count: number;
  infant_count: number;
  cabin_class: string;
  currency: string;
  expires_at?: string | null;
};

export default function Results({ search, offers }: { search: SearchSummary; offers: FlightOffer[] }) {
  return (
    <PublicLayout>
      <section className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
          <div>
            <p className="text-sm font-bold uppercase tracking-[0.25em] text-slate-500">Search {search.reference}</p>
            <h1 className="mt-2 text-3xl font-black text-slate-950">
              {search.origin} → {search.destination}
            </h1>
            <p className="mt-2 text-sm text-slate-600">
              {search.adult_count + search.child_count + search.infant_count} passenger(s), {search.cabin_class.replace('_', ' ')}, {search.trip_type.replace('_', ' ')}
            </p>
          </div>
          <a href="/flights/search" className="rounded-2xl border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">
            Modify search
          </a>
        </div>

        <div className="mt-8 grid gap-4">
          {offers.length === 0 ? (
            <div className="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center">
              <h2 className="text-xl font-bold">No offers returned</h2>
              <p className="mt-2 text-slate-500">Try another route/date or verify provider credentials.</p>
            </div>
          ) : (
            offers.map((offer) => <FlightResultCard key={offer.id} offer={offer} />)
          )}
        </div>
      </section>
    </PublicLayout>
  );
}
