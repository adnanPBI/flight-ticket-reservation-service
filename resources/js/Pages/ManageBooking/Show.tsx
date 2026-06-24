import PublicLayout from '@/Components/Layout/PublicLayout';
import BookingTimeline from '@/Components/Flights/BookingTimeline';
import { formatMoney } from '@/Components/Flights/money';

 type Segment = {
  airline_code?: string | null;
  flight_number?: string | null;
  origin: string;
  destination: string;
  departure_at?: string | null;
  arrival_at?: string | null;
  duration_minutes?: number | null;
  cabin_class?: string | null;
};

type Passenger = { passenger_type: string; title?: string | null; first_name: string; last_name: string; nationality?: string | null };
type Payment = { id: number; provider: string; provider_payment_id?: string | null; status: string; currency: string; amount_minor: number; refunded_amount_minor: number; paid_at?: string | null };
type EventRow = { from_status?: string | null; to_status: string; reason?: string | null; created_at: string };
type PriceRow = { label: string; type: string; currency: string; amount_minor: number };

type Booking = {
  reference: string;
  status: string;
  provider: string;
  provider_order_id?: string | null;
  provider_order_status?: string | null;
  pnr?: string | null;
  ticket_number?: string | null;
  currency: string;
  total_amount_minor: number;
  customer_email?: string | null;
  customer_phone?: string | null;
  failure_reason?: string | null;
  confirmed_at?: string | null;
  ticketed_at?: string | null;
  manage_receipt_url: string;
  segments: Segment[];
  passengers: Passenger[];
  payments: Payment[];
  price_breakdowns: PriceRow[];
  events: EventRow[];
};

function statusStep(status: string): number {
  if (['search_created', 'offer_selected'].includes(status)) return 0;
  if (status === 'passenger_details_added') return 1;
  if (['payment_pending', 'payment_succeeded'].includes(status)) return 2;
  if (['booking_confirming', 'booking_confirmed', 'ticketing_pending'].includes(status)) return 3;
  return 4;
}

export default function Show({ booking }: { booking: Booking }) {
  return (
    <PublicLayout>
      <section className="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <BookingTimeline current={statusStep(booking.status)} />

        <div className="mt-6 grid gap-6 lg:grid-cols-[1fr_340px]">
          <div className="space-y-6">
            <div className="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
              <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                <div>
                  <p className="text-sm font-bold uppercase tracking-[0.25em] text-slate-500">Manage booking</p>
                  <h1 className="mt-2 text-3xl font-black">{booking.reference}</h1>
                  <p className="mt-2 text-slate-600">Status: <strong>{booking.status}</strong></p>
                </div>
                <a href={booking.manage_receipt_url} target="_blank" className="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white" rel="noreferrer">
                  View receipt
                </a>
              </div>

              {booking.failure_reason && (
                <p className="mt-5 rounded-2xl bg-rose-50 p-4 text-sm font-bold text-rose-700">{booking.failure_reason}</p>
              )}

              <dl className="mt-6 grid gap-4 sm:grid-cols-2">
                <div><dt className="text-xs font-bold uppercase text-slate-500">Provider</dt><dd className="font-bold">{booking.provider}</dd></div>
                <div><dt className="text-xs font-bold uppercase text-slate-500">Provider order status</dt><dd className="font-bold">{booking.provider_order_status ?? 'Not created'}</dd></div>
                <div><dt className="text-xs font-bold uppercase text-slate-500">PNR</dt><dd className="font-bold">{booking.pnr ?? 'Pending'}</dd></div>
                <div><dt className="text-xs font-bold uppercase text-slate-500">Ticket</dt><dd className="font-bold">{booking.ticket_number ?? 'Pending'}</dd></div>
                <div><dt className="text-xs font-bold uppercase text-slate-500">Confirmed</dt><dd>{booking.confirmed_at ?? 'Pending'}</dd></div>
                <div><dt className="text-xs font-bold uppercase text-slate-500">Ticketed</dt><dd>{booking.ticketed_at ?? 'Pending'}</dd></div>
              </dl>
            </div>

            <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
              <h2 className="text-xl font-black">Flight segments</h2>
              <div className="mt-4 space-y-3">
                {booking.segments.map((segment, index) => (
                  <div key={`${segment.origin}-${segment.destination}-${index}`} className="rounded-2xl border border-slate-100 p-4">
                    <div className="flex flex-col justify-between gap-2 sm:flex-row">
                      <p className="font-black">{segment.origin} → {segment.destination}</p>
                      <p className="text-sm font-bold text-slate-500">{segment.airline_code ?? 'Airline'} {segment.flight_number ?? ''}</p>
                    </div>
                    <p className="mt-2 text-sm text-slate-600">{segment.departure_at ? new Date(segment.departure_at).toLocaleString() : 'Departure pending'} → {segment.arrival_at ? new Date(segment.arrival_at).toLocaleString() : 'Arrival pending'}</p>
                    <p className="mt-1 text-xs font-bold uppercase text-slate-400">{segment.cabin_class ?? 'Cabin pending'}</p>
                  </div>
                ))}
              </div>
            </div>

            <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
              <h2 className="text-xl font-black">Booking timeline</h2>
              <div className="mt-4 space-y-3">
                {booking.events.map((event, index) => (
                  <div key={`${event.created_at}-${index}`} className="rounded-2xl bg-slate-50 p-4 text-sm">
                    <p className="font-black">{event.from_status ?? 'new'} → {event.to_status}</p>
                    <p className="mt-1 text-slate-600">{event.reason ?? 'Status update'}</p>
                    <p className="mt-1 text-xs text-slate-400">{event.created_at}</p>
                  </div>
                ))}
              </div>
            </div>
          </div>

          <aside className="space-y-6">
            <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
              <p className="text-xs font-bold uppercase text-slate-500">Total paid/owed</p>
              <p className="mt-1 text-3xl font-black">{formatMoney(booking.total_amount_minor, booking.currency)}</p>
              <p className="mt-3 text-sm text-slate-500">Customer: {booking.customer_email}</p>
              <p className="text-sm text-slate-500">Phone: {booking.customer_phone ?? 'N/A'}</p>
            </div>

            <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
              <h2 className="font-black">Passengers</h2>
              <div className="mt-3 space-y-2">
                {booking.passengers.map((passenger, index) => (
                  <div key={`${passenger.first_name}-${index}`} className="rounded-2xl bg-slate-50 p-3 text-sm">
                    <p className="font-bold">{passenger.title ? `${passenger.title} ` : ''}{passenger.first_name} {passenger.last_name}</p>
                    <p className="text-slate-500">{passenger.passenger_type}</p>
                  </div>
                ))}
              </div>
            </div>

            <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
              <h2 className="font-black">Payments</h2>
              <div className="mt-3 space-y-2">
                {booking.payments.length === 0 && <p className="text-sm text-slate-500">No payment records yet.</p>}
                {booking.payments.map((payment) => (
                  <div key={payment.id} className="rounded-2xl bg-slate-50 p-3 text-sm">
                    <p className="font-bold">{payment.status}</p>
                    <p className="text-slate-500">{formatMoney(payment.amount_minor, payment.currency)}</p>
                  </div>
                ))}
              </div>
            </div>

            <div className="rounded-3xl border border-blue-100 bg-blue-50 p-6 text-sm text-blue-900">
              <h2 className="font-black">Need help?</h2>
              <p className="mt-2">Use the floating support chat. Mention booking reference <strong>{booking.reference}</strong> so the admin team can trace the conversation.</p>
            </div>
          </aside>
        </div>
      </section>
    </PublicLayout>
  );
}
