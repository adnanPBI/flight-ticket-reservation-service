import { FormEvent, useMemo, useState } from 'react';
import { loadStripe } from '@stripe/stripe-js';
import { Elements, PaymentElement, useElements, useStripe } from '@stripe/react-stripe-js';
import { formatMoney } from '@/Components/Flights/money';

type PaymentPayload = {
  id: number;
  provider: string;
  provider_payment_id?: string | null;
  status: string;
  currency: string;
  amount_minor: number;
  client_secret?: string | null;
  mock_mode: boolean;
  publishable_key?: string | null;
  safe_note?: string | null;
};

type IntentResponse = {
  payment: PaymentPayload;
  booking: { reference: string; status: string };
  mock_success_url: string;
  confirmation_url: string;
};

type Props = {
  bookingReference: string;
  amountMinor: number;
  currency: string;
  intentUrl: string;
  confirmationUrl: string;
  initialPayment?: PaymentPayload | null;
  phaseNote: string;
};

function csrfToken(): string {
  return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}

async function postJson(url: string): Promise<IntentResponse> {
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken(),
    },
    body: JSON.stringify({}),
  });

  const data = await response.json();
  if (!response.ok) {
    throw new Error(data.message ?? 'Payment request failed.');
  }

  return data;
}

function RealStripeForm({ confirmationUrl }: { confirmationUrl: string }) {
  const stripe = useStripe();
  const elements = useElements();
  const [processing, setProcessing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function submit(event: FormEvent) {
    event.preventDefault();
    setError(null);

    if (!stripe || !elements) {
      setError('Stripe is still loading. Try again in a moment.');
      return;
    }

    setProcessing(true);

    const result = await stripe.confirmPayment({
      elements,
      confirmParams: {
        return_url: window.location.origin + confirmationUrl,
      },
    });

    if (result.error) {
      setError(result.error.message ?? 'Payment could not be completed.');
      setProcessing(false);
    }
  }

  return (
    <form onSubmit={submit} className="space-y-4">
      <PaymentElement />
      {error && <p className="rounded-2xl bg-rose-50 p-3 text-sm font-semibold text-rose-700">{error}</p>}
      <button
        type="submit"
        disabled={!stripe || processing}
        className="w-full rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white disabled:cursor-not-allowed disabled:opacity-60"
      >
        {processing ? 'Processing...' : 'Pay securely'}
      </button>
    </form>
  );
}

export default function StripePaymentPanel({
  bookingReference,
  amountMinor,
  currency,
  intentUrl,
  confirmationUrl,
  initialPayment,
  phaseNote,
}: Props) {
  const [intent, setIntent] = useState<IntentResponse | null>(initialPayment ? {
    payment: initialPayment,
    booking: { reference: bookingReference, status: 'payment_pending' },
    mock_success_url: '',
    confirmation_url: confirmationUrl,
  } : null);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const stripePromise = useMemo(() => {
    if (!intent?.payment.publishable_key || intent.payment.mock_mode || !intent.payment.client_secret) {
      return null;
    }

    return loadStripe(intent.payment.publishable_key);
  }, [intent?.payment.publishable_key, intent?.payment.mock_mode, intent?.payment.client_secret]);

  async function preparePayment() {
    setLoading(true);
    setError(null);
    setMessage(null);

    try {
      const data = await postJson(intentUrl);
      setIntent(data);
      setMessage(data.payment.safe_note ?? 'PaymentIntent prepared.');
    } catch (paymentError) {
      setError(paymentError instanceof Error ? paymentError.message : 'Payment preparation failed.');
    } finally {
      setLoading(false);
    }
  }

  async function simulateSuccess() {
    if (!intent?.mock_success_url) {
      setError('Mock success URL is missing. Re-create the mock payment first.');
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const data = await postJson(intent.mock_success_url);
      window.location.href = data.confirmation_url ?? confirmationUrl;
    } catch (paymentError) {
      setError(paymentError instanceof Error ? paymentError.message : 'Mock payment simulation failed.');
    } finally {
      setLoading(false);
    }
  }

  const activePayment = intent?.payment ?? initialPayment;

  return (
    <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
      <div className="flex items-start justify-between gap-4">
        <div>
          <p className="text-xs font-bold uppercase tracking-[0.25em] text-slate-500">Payment</p>
          <h2 className="mt-2 text-2xl font-black">Secure checkout</h2>
          <p className="mt-2 text-sm text-slate-600">{phaseNote}</p>
        </div>
        <div className="rounded-2xl bg-slate-50 px-4 py-3 text-right">
          <p className="text-xs font-bold uppercase text-slate-500">Total</p>
          <p className="text-xl font-black">{formatMoney(amountMinor, currency)}</p>
        </div>
      </div>

      {message && <p className="mt-4 rounded-2xl bg-emerald-50 p-3 text-sm font-semibold text-emerald-700">{message}</p>}
      {error && <p className="mt-4 rounded-2xl bg-rose-50 p-3 text-sm font-semibold text-rose-700">{error}</p>}

      <div className="mt-5 rounded-2xl border border-dashed border-slate-300 p-4 text-sm text-slate-600">
        <p><span className="font-bold text-slate-900">Booking:</span> {bookingReference}</p>
        <p><span className="font-bold text-slate-900">Payment status:</span> {activePayment?.status ?? 'not_created'}</p>
        <p><span className="font-bold text-slate-900">Mode:</span> {activePayment?.mock_mode ?? true ? 'safe mock mode' : 'real Stripe mode'}</p>
      </div>

      {!activePayment?.client_secret && activePayment?.status !== 'succeeded' && (
        <button
          type="button"
          onClick={preparePayment}
          disabled={loading}
          className="mt-5 w-full rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white disabled:cursor-not-allowed disabled:opacity-60"
        >
          {loading ? 'Preparing...' : 'Prepare secure payment'}
        </button>
      )}

      {activePayment?.mock_mode && activePayment?.client_secret && activePayment.status !== 'succeeded' && (
        <div className="mt-5 rounded-2xl bg-amber-50 p-4">
          <h3 className="font-black text-amber-900">Safe mock checkout</h3>
          <p className="mt-2 text-sm text-amber-800">
            This does not call Stripe and cannot charge a card. It only tests the internal payment/booking status flow.
          </p>
          <button
            type="button"
            onClick={simulateSuccess}
            disabled={loading}
            className="mt-4 w-full rounded-2xl bg-amber-900 px-5 py-3 text-sm font-black text-white disabled:cursor-not-allowed disabled:opacity-60"
          >
            {loading ? 'Simulating...' : 'Simulate successful payment'}
          </button>
        </div>
      )}

      {!activePayment?.mock_mode && activePayment?.client_secret && stripePromise && (
        <div className="mt-5 rounded-2xl border border-slate-200 p-4">
          <Elements stripe={stripePromise} options={{ clientSecret: activePayment.client_secret }}>
            <RealStripeForm confirmationUrl={confirmationUrl} />
          </Elements>
        </div>
      )}

      {activePayment?.status === 'succeeded' && (
        <a href={confirmationUrl} className="mt-5 block w-full rounded-2xl bg-emerald-600 px-5 py-3 text-center text-sm font-black text-white">
          Continue to confirmation
        </a>
      )}
    </div>
  );
}
