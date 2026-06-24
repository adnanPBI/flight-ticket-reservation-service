import PublicLayout from '@/Components/Layout/PublicLayout';
import BookingTimeline from '@/Components/Flights/BookingTimeline';
import StripePaymentPanel from '@/Components/Payments/StripePaymentPanel';
import { formatMoney } from '@/Components/Flights/money';
import { useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';

function inputClass() {
  return 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-slate-950';
}

type Booking = {
  reference: string;
  status: string;
  currency: string;
  total_amount_minor: number;
  markup_amount_minor?: number;
  discount_amount_minor?: number;
  applied_promo_code?: string | null;
  pricing_locked_at?: string | null;
  customer_email?: string | null;
  customer_phone?: string | null;
  passenger_count: number;
  passengers: Array<{ passenger_type: string; first_name: string; last_name: string }>;
  price_breakdown: Array<{ label: string; type: string; currency: string; amount_minor: number }>;
};

type Payment = {
  id: number;
  provider: string;
  provider_payment_id?: string | null;
  status: string;
  currency: string;
  amount_minor: number;
  client_secret?: string | null;
  mock_mode: boolean;
  publishable_key?: string | null;
} | null;

type PaymentConfig = {
  mock_mode: boolean;
  promo_apply_url: string;
  intent_url: string;
  confirmation_url: string;
  publishable_key: string;
  phase_note: string;
};

type FlashProps = { success?: string };

export default function Checkout({ booking, payment, paymentConfig }: { booking: Booking; payment: Payment; paymentConfig: PaymentConfig }) {
  const { props } = usePage<{ flash?: FlashProps; errors?: Record<string, string> }>();
  const promoForm = useForm({ code: booking.applied_promo_code ?? '' });

  function applyPromo(event: FormEvent) {
    event.preventDefault();
    promoForm.post(paymentConfig.promo_apply_url, {
      preserveScroll: true,
    });
  }

  return (
    <PublicLayout>
      <section className="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <BookingTimeline current={2} />
        <div className="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]">
          <div className="space-y-6">
            <div className="rounded-3xl border border-slate-200 bg-white p-8">
              <p className="text-sm font-bold uppercase tracking-[0.25em] text-slate-500">Checkout</p>
              <h1 className="mt-2 text-3xl font-black">Review and pay</h1>
              <p className="mt-3 text-slate-600">
                Payment is prepared from the saved booking snapshot. Promo and markup logic is calculated server-side before creating the PaymentIntent.
              </p>

              <div className="mt-6 rounded-2xl bg-slate-50 p-5">
                <p className="text-sm text-slate-500">Booking {booking.reference}</p>
                <p className="mt-1 text-3xl font-black">{formatMoney(booking.total_amount_minor, booking.currency)}</p>
                <p className="mt-2 text-sm font-semibold text-slate-500">Status: {booking.status}</p>
                {booking.applied_promo_code && (
                  <p className="mt-2 inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">
                    Promo applied: {booking.applied_promo_code}
                  </p>
                )}
              </div>
            </div>

            <div className="rounded-3xl border border-slate-200 bg-white p-6">
              <div className="flex items-start justify-between gap-4">
                <div>
                  <h2 className="text-lg font-black">Promo code</h2>
                  <p className="mt-1 text-sm text-slate-500">Applying a promo reprices the booking and cancels any unpaid old PaymentIntent.</p>
                </div>
                <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">Phase 9</span>
              </div>
              <form onSubmit={applyPromo} className="mt-4 flex flex-col gap-3 sm:flex-row">
                <input
                  value={promoForm.data.code}
                  onChange={(event) => promoForm.setData('code', event.target.value.toUpperCase())}
                  placeholder="Example: WELCOME10"
                  className={inputClass()}
                />
                <button
                  disabled={promoForm.processing}
                  className="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white disabled:opacity-60"
                >
                  {promoForm.processing ? 'Applying…' : 'Apply'}
                </button>
              </form>
              {props.errors?.promo_code && <p className="mt-3 text-sm font-bold text-rose-600">{props.errors.promo_code}</p>}
              {props.flash?.success && <p className="mt-3 text-sm font-bold text-emerald-700">{props.flash.success}</p>}
            </div>

            <StripePaymentPanel
              bookingReference={booking.reference}
              amountMinor={booking.total_amount_minor}
              currency={booking.currency}
              intentUrl={paymentConfig.intent_url}
              confirmationUrl={paymentConfig.confirmation_url}
              initialPayment={payment}
              phaseNote={paymentConfig.phase_note}
            />
          </div>

          <aside className="space-y-4">
            <div className="rounded-3xl border border-slate-200 bg-white p-5">
              <h2 className="font-black">Customer</h2>
              <p className="mt-3 text-sm text-slate-600">{booking.customer_email}</p>
              <p className="text-sm text-slate-600">{booking.customer_phone}</p>
            </div>

            <div className="rounded-3xl border border-slate-200 bg-white p-5">
              <h2 className="font-black">Passengers</h2>
              <div className="mt-3 space-y-2 text-sm">
                {booking.passengers.map((passenger, index) => (
                  <div key={`${passenger.first_name}-${index}`} className="rounded-2xl bg-slate-50 p-3">
                    <p className="font-bold">{passenger.first_name} {passenger.last_name}</p>
                    <p className="text-slate-500">{passenger.passenger_type}</p>
                  </div>
                ))}
              </div>
            </div>

            <div className="rounded-3xl border border-slate-200 bg-white p-5">
              <h2 className="font-black">Price</h2>
              <div className="mt-3 space-y-2 text-sm">
                {booking.price_breakdown.map((item) => (
                  <div key={`${item.type}-${item.label}`} className="flex items-center justify-between gap-3">
                    <span className="text-slate-500">{item.label}</span>
                    <span className="font-bold">{formatMoney(item.amount_minor, item.currency)}</span>
                  </div>
                ))}
              </div>
              {booking.pricing_locked_at && (
                <p className="mt-4 text-xs font-semibold text-slate-400">Pricing locked: {new Date(booking.pricing_locked_at).toLocaleString()}</p>
              )}
            </div>
          </aside>
        </div>
      </section>
    </PublicLayout>
  );
}
