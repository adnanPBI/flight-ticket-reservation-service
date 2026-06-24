import PublicLayout from '@/Components/Layout/PublicLayout';
import BookingTimeline from '@/Components/Flights/BookingTimeline';
import PassengerForm, { type PassengerInput } from '@/Components/Flights/PassengerForm';
import { formatDateTime, formatMoney } from '@/Components/Flights/money';

type Booking = {
  reference: string;
  status: string;
  currency: string;
  total_amount_minor: number;
  customer_email?: string | null;
  customer_phone?: string | null;
  offer_expires_at?: string | null;
  passenger_count: number;
  passenger_counts: {
    adult: number;
    child: number;
    infant_without_seat: number;
  };
  passengers: Partial<PassengerInput>[];
  price_breakdown: Array<{ label: string; type: string; currency: string; amount_minor: number }>;
  offer?: {
    airline_name?: string | null;
    airline_code?: string | null;
    origin: string;
    destination: string;
    departure_at?: string | null;
    arrival_at?: string | null;
    baggage_summary?: string | null;
    refundability?: string | null;
  } | null;
  segments: Array<{
    airline_code?: string | null;
    flight_number?: string | null;
    origin: string;
    destination: string;
    departure_at?: string | null;
    arrival_at?: string | null;
    duration_minutes?: number | null;
    cabin_class?: string | null;
  }>;
};

export default function PassengerDetails({ booking }: { booking: Booking }) {
  return (
    <PublicLayout>
      <section className="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <BookingTimeline current={1} />
        <div className="mt-6 grid gap-6 lg:grid-cols-[1fr_360px]">
          <div className="rounded-3xl border border-slate-200 bg-white p-6">
            <p className="text-sm font-bold uppercase tracking-[0.25em] text-slate-500">Booking {booking.reference}</p>
            <h1 className="mt-2 text-3xl font-black">Passenger details</h1>
            <p className="mt-2 text-slate-600">
              Phase 5 stores validated passenger/contact data and moves the booking to passenger_details_added.
            </p>

            {booking.offer_expires_at && (
              <div className="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800">
                Selected fare expires at {formatDateTime(booking.offer_expires_at)}. Save details before expiry or search again.
              </div>
            )}

            <div className="mt-6">
              <PassengerForm
                bookingReference={booking.reference}
                passengerCounts={booking.passenger_counts}
                customerEmail={booking.customer_email}
                customerPhone={booking.customer_phone}
                existingPassengers={booking.passengers}
              />
            </div>
          </div>

          <aside className="space-y-4">
            <div className="rounded-3xl border border-slate-200 bg-white p-5">
              <h2 className="font-black">Trip summary</h2>
              {booking.offer && (
                <div className="mt-4 text-sm text-slate-600">
                  <p className="font-bold text-slate-950">{booking.offer.airline_name ?? booking.offer.airline_code ?? 'Airline'}</p>
                  <p>{booking.offer.origin} → {booking.offer.destination}</p>
                  <p>{formatDateTime(booking.offer.departure_at)}</p>
                  <p className="mt-2">{booking.offer.baggage_summary ?? 'Check airline baggage rules'}</p>
                  <p>{booking.offer.refundability ?? 'Check fare rules'}</p>
                </div>
              )}

              <div className="mt-5 border-t border-slate-200 pt-4">
                <p className="text-xs font-bold uppercase text-slate-500">Total</p>
                <p className="text-2xl font-black">{formatMoney(booking.total_amount_minor, booking.currency)}</p>
              </div>
            </div>

            <div className="rounded-3xl border border-slate-200 bg-white p-5">
              <h2 className="font-black">Price snapshot</h2>
              <div className="mt-3 space-y-2 text-sm">
                {booking.price_breakdown.map((item) => (
                  <div key={`${item.type}-${item.label}`} className="flex items-center justify-between gap-3">
                    <span className="text-slate-500">{item.label}</span>
                    <span className="font-bold">{formatMoney(item.amount_minor, item.currency)}</span>
                  </div>
                ))}
              </div>
            </div>

            <div className="rounded-3xl border border-slate-200 bg-white p-5">
              <h2 className="font-black">Segments</h2>
              <div className="mt-3 space-y-3 text-sm">
                {booking.segments.map((segment, index) => (
                  <div key={`${segment.origin}-${segment.destination}-${index}`} className="rounded-2xl bg-slate-50 p-3">
                    <p className="font-bold text-slate-950">{segment.origin} → {segment.destination}</p>
                    <p className="text-slate-500">{formatDateTime(segment.departure_at)} → {formatDateTime(segment.arrival_at)}</p>
                    <p className="text-slate-500">{segment.airline_code} {segment.flight_number}</p>
                  </div>
                ))}
              </div>
            </div>
          </aside>
        </div>
      </section>
    </PublicLayout>
  );
}
