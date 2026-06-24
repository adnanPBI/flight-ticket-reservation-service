import { router } from '@inertiajs/react';
import PublicLayout from '@/Components/Layout/PublicLayout';
import PriceBreakdown from '@/Components/Flights/PriceBreakdown';
import { formatDateTime, formatDuration } from '@/Components/Flights/money';

type OfferDetails = {
  id: number;
  airline_code?: string | null;
  airline_name?: string | null;
  origin: string;
  destination: string;
  departure_at?: string | null;
  arrival_at?: string | null;
  duration_minutes?: number | null;
  stops: number;
  fare_brand?: string | null;
  baggage_summary?: string | null;
  refundability?: string | null;
  currency: string;
  base_amount_minor: number;
  tax_amount_minor: number;
  fee_amount_minor: number;
  markup_amount_minor: number;
  discount_amount_minor: number;
  total_amount_minor: number;
  segments: Array<Record<string, string | number | null>>;
};

export default function FareDetails({ offer }: { offer: OfferDetails }) {
  return (
    <PublicLayout>
      <section className="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8">
        <div className="rounded-3xl border border-slate-200 bg-white p-6">
          <p className="text-sm font-bold uppercase tracking-[0.25em] text-slate-500">Fare details</p>
          <h1 className="mt-2 text-3xl font-black text-slate-950">{offer.airline_name ?? offer.airline_code ?? 'Airline'}</h1>

          <div className="mt-6 grid gap-4 rounded-3xl bg-slate-50 p-5 md:grid-cols-3">
            <div>
              <p className="text-2xl font-black">{offer.origin}</p>
              <p className="text-sm text-slate-500">{formatDateTime(offer.departure_at)}</p>
            </div>
            <div className="text-sm text-slate-600 md:text-center">
              <p className="font-bold">{formatDuration(offer.duration_minutes)}</p>
              <p>{offer.stops === 0 ? 'Non-stop' : `${offer.stops} stop(s)`}</p>
            </div>
            <div className="md:text-right">
              <p className="text-2xl font-black">{offer.destination}</p>
              <p className="text-sm text-slate-500">{formatDateTime(offer.arrival_at)}</p>
            </div>
          </div>

          <div className="mt-6 grid gap-3 md:grid-cols-3">
            <div className="rounded-2xl border border-slate-200 p-4">
              <p className="text-xs font-bold uppercase text-slate-500">Baggage</p>
              <p className="mt-1 text-sm text-slate-700">{offer.baggage_summary ?? 'Check airline rules'}</p>
            </div>
            <div className="rounded-2xl border border-slate-200 p-4">
              <p className="text-xs font-bold uppercase text-slate-500">Refundability</p>
              <p className="mt-1 text-sm text-slate-700">{offer.refundability ?? 'Check fare rules'}</p>
            </div>
            <div className="rounded-2xl border border-slate-200 p-4">
              <p className="text-xs font-bold uppercase text-slate-500">Fare brand</p>
              <p className="mt-1 text-sm text-slate-700">{offer.fare_brand ?? 'Standard'}</p>
            </div>
          </div>
        </div>

        <aside className="space-y-4">
          <PriceBreakdown {...offer} />
          <button onClick={() => router.post(`/flights/offers/${offer.id}/select`)} className="w-full rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white hover:bg-slate-800">
            Continue with this fare
          </button>
        </aside>
      </section>
    </PublicLayout>
  );
}
