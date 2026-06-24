import { Link, router } from '@inertiajs/react';
import { formatDateTime, formatDuration, formatMoney } from './money';

export type FlightOffer = {
  id: number;
  provider: string;
  provider_offer_id: string;
  airline_code?: string | null;
  airline_name?: string | null;
  origin: string;
  destination: string;
  departure_at?: string | null;
  arrival_at?: string | null;
  duration_minutes?: number | null;
  stops: number;
  cabin_class: string;
  fare_brand?: string | null;
  baggage_summary?: string | null;
  refundability?: string | null;
  currency: string;
  total_amount_minor: number;
  expires_at?: string | null;
};

export default function FlightResultCard({ offer }: { offer: FlightOffer }) {
  function selectOffer() {
    router.post(`/flights/offers/${offer.id}/select`);
  }

  return (
    <article className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
      <div className="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
        <div className="min-w-0 flex-1">
          <div className="flex flex-wrap items-center gap-2">
            <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{offer.provider.toUpperCase()}</span>
            <span className="text-sm font-semibold text-slate-900">{offer.airline_name ?? offer.airline_code ?? 'Airline TBA'}</span>
            {offer.fare_brand && <span className="text-xs text-slate-500">{offer.fare_brand}</span>}
          </div>

          <div className="mt-4 grid gap-4 sm:grid-cols-[1fr_auto_1fr] sm:items-center">
            <div>
              <p className="text-2xl font-bold">{offer.origin}</p>
              <p className="text-sm text-slate-500">{formatDateTime(offer.departure_at)}</p>
            </div>
            <div className="text-left sm:text-center">
              <p className="text-sm font-semibold text-slate-700">{formatDuration(offer.duration_minutes)}</p>
              <div className="my-2 h-px w-28 bg-slate-200" />
              <p className="text-xs text-slate-500">{offer.stops === 0 ? 'Non-stop' : `${offer.stops} stop${offer.stops > 1 ? 's' : ''}`}</p>
            </div>
            <div className="sm:text-right">
              <p className="text-2xl font-bold">{offer.destination}</p>
              <p className="text-sm text-slate-500">{formatDateTime(offer.arrival_at)}</p>
            </div>
          </div>

          <div className="mt-4 flex flex-wrap gap-2 text-xs text-slate-600">
            <span className="rounded-full bg-slate-50 px-3 py-1 ring-1 ring-slate-200">{offer.cabin_class.replace('_', ' ')}</span>
            <span className="rounded-full bg-slate-50 px-3 py-1 ring-1 ring-slate-200">{offer.baggage_summary ?? 'Baggage TBA'}</span>
            <span className="rounded-full bg-slate-50 px-3 py-1 ring-1 ring-slate-200">{offer.refundability ?? 'Fare rules TBA'}</span>
          </div>
        </div>

        <div className="rounded-3xl bg-slate-50 p-4 md:w-56">
          <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Total price</p>
          <p className="mt-1 text-3xl font-black text-slate-950">{formatMoney(offer.total_amount_minor, offer.currency)}</p>
          <div className="mt-4 grid gap-2">
            <Link href={`/flights/offers/${offer.id}`} className="rounded-2xl border border-slate-300 px-4 py-2 text-center text-sm font-bold text-slate-700 hover:bg-white">
              View details
            </Link>
            <button onClick={selectOffer} className="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800">
              Select fare
            </button>
          </div>
        </div>
      </div>
    </article>
  );
}
