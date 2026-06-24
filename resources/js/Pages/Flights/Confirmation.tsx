import PublicLayout from '@/Components/Layout/PublicLayout';
import BookingTimeline from '@/Components/Flights/BookingTimeline';
import { formatMoney } from '@/Components/Flights/money';

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
  failure_reason?: string | null;
  confirmed_at?: string | null;
  ticketed_at?: string | null;
  phase_note: string;
};

type Payment = {
  id: number;
  status: string;
  provider_payment_id?: string | null;
  amount_minor: number;
  currency: string;
  mock_mode: boolean;
} | null;

type EventRow = {
  from_status?: string | null;
  to_status: string;
  reason?: string | null;
  created_at: string;
};

export default function Confirmation({ booking, payment, events }: { booking: Booking; payment: Payment; events: EventRow[] }) {
  const isActionRequired = booking.status === 'ticketing_pending' && booking.provider_order_status === 'manual_review_required';

  return (
    <PublicLayout>
      <section className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <BookingTimeline current={4} />
        <div className="mt-6 rounded-3xl border border-slate-200 bg-white p-8">
          <p className="text-sm font-bold uppercase tracking-[0.25em] text-slate-500">Confirmation</p>
          <h1 className="mt-2 text-3xl font-black">Booking status</h1>
          <p className="mt-3 rounded-2xl bg-blue-50 p-4 text-sm font-semibold text-blue-800">{booking.phase_note}</p>

          {isActionRequired && (
            <p className="mt-3 rounded-2xl bg-amber-50 p-4 text-sm font-semibold text-amber-800">
              Real provider order finalization is disabled. Payment can be tested safely, but a real Duffel order will not be created until the safety gate is enabled.
            </p>
          )}

          {booking.failure_reason && (
            <p className="mt-3 rounded-2xl bg-red-50 p-4 text-sm font-semibold text-red-700">{booking.failure_reason}</p>
          )}

          <dl className="mt-6 grid gap-4 md:grid-cols-2">
            <div><dt className="text-xs font-bold uppercase text-slate-500">Reference</dt><dd className="font-bold">{booking.reference}</dd></div>
            <div><dt className="text-xs font-bold uppercase text-slate-500">Booking Status</dt><dd className="font-bold">{booking.status}</dd></div>
            <div><dt className="text-xs font-bold uppercase text-slate-500">Payment Status</dt><dd>{payment?.status ?? 'not_created'}</dd></div>
            <div><dt className="text-xs font-bold uppercase text-slate-500">Payment Mode</dt><dd>{payment?.mock_mode ? 'safe mock mode' : 'real/test Stripe mode'}</dd></div>
            <div><dt className="text-xs font-bold uppercase text-slate-500">Provider</dt><dd>{booking.provider}</dd></div>
            <div><dt className="text-xs font-bold uppercase text-slate-500">Provider Order Status</dt><dd>{booking.provider_order_status ?? 'not_created'}</dd></div>
            <div><dt className="text-xs font-bold uppercase text-slate-500">Provider Order ID</dt><dd className="break-all">{booking.provider_order_id ?? 'N/A'}</dd></div>
            <div><dt className="text-xs font-bold uppercase text-slate-500">Stripe PaymentIntent</dt><dd className="break-all">{payment?.provider_payment_id ?? 'N/A'}</dd></div>
            <div><dt className="text-xs font-bold uppercase text-slate-500">PNR</dt><dd>{booking.pnr ?? 'Pending'}</dd></div>
            <div><dt className="text-xs font-bold uppercase text-slate-500">Ticket Number</dt><dd>{booking.ticket_number ?? 'Pending'}</dd></div>
            <div><dt className="text-xs font-bold uppercase text-slate-500">Total</dt><dd>{formatMoney(booking.total_amount_minor, booking.currency)}</dd></div>
          </dl>
        </div>

        <div className="mt-6 rounded-3xl border border-slate-200 bg-white p-6">
          <h2 className="text-lg font-black">Recent booking events</h2>
          <div className="mt-4 space-y-3">
            {events.length === 0 && <p className="text-sm text-slate-500">No events recorded yet.</p>}
            {events.map((event, index) => (
              <div key={`${event.created_at}-${index}`} className="rounded-2xl border border-slate-100 p-4 text-sm">
                <p className="font-bold">{event.from_status ?? 'new'} → {event.to_status}</p>
                <p className="text-slate-600">{event.reason ?? 'Status update'}</p>
                <p className="mt-1 text-xs text-slate-400">{event.created_at}</p>
              </div>
            ))}
          </div>
        </div>
      </section>
    </PublicLayout>
  );
}
