import PublicLayout from '@/Components/Layout/PublicLayout';
import { useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';

function inputClass() {
  return 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-slate-950';
}

type PageProps = { errors?: Record<string, string> };

export default function Lookup() {
  const { props } = usePage<PageProps>();
  const form = useForm({ booking_reference: '', customer_email: '' });

  function submit(event: FormEvent) {
    event.preventDefault();
    form.post('/manage-booking');
  }

  return (
    <PublicLayout>
      <section className="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_420px] lg:px-8">
        <div className="rounded-3xl bg-slate-950 p-8 text-white">
          <p className="text-sm font-bold uppercase tracking-[0.25em] text-slate-300">Manage booking</p>
          <h1 className="mt-3 text-4xl font-black tracking-tight">Find your trip safely.</h1>
          <p className="mt-4 max-w-2xl text-slate-300">
            Enter the booking reference and customer email used during checkout. This avoids exposing booking details through a public reference-only URL.
          </p>
          <div className="mt-8 grid gap-3 text-sm text-slate-200 sm:grid-cols-3">
            <div className="rounded-2xl bg-white/10 p-4"><strong>1.</strong> Verify booking</div>
            <div className="rounded-2xl bg-white/10 p-4"><strong>2.</strong> View payment and ticketing</div>
            <div className="rounded-2xl bg-white/10 p-4"><strong>3.</strong> Open support chat</div>
          </div>
        </div>

        <form onSubmit={submit} className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <h2 className="text-2xl font-black">Lookup booking</h2>
          <p className="mt-2 text-sm text-slate-500">Example reference format: OTA-XXXXXX</p>

          <label className="mt-6 block text-sm font-black text-slate-700">Booking reference</label>
          <input
            className={inputClass() + ' mt-2 uppercase'}
            value={form.data.booking_reference}
            onChange={(event) => form.setData('booking_reference', event.target.value.toUpperCase())}
            placeholder="OTA-ABC123"
          />
          {(form.errors.booking_reference || props.errors?.booking_reference) && (
            <p className="mt-2 text-sm font-bold text-rose-600">{form.errors.booking_reference ?? props.errors?.booking_reference}</p>
          )}

          <label className="mt-5 block text-sm font-black text-slate-700">Customer email</label>
          <input
            className={inputClass() + ' mt-2'}
            value={form.data.customer_email}
            onChange={(event) => form.setData('customer_email', event.target.value.toLowerCase())}
            placeholder="customer@example.com"
            type="email"
          />
          {form.errors.customer_email && <p className="mt-2 text-sm font-bold text-rose-600">{form.errors.customer_email}</p>}

          <button
            disabled={form.processing}
            className="mt-6 w-full rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white disabled:opacity-60"
          >
            {form.processing ? 'Checking…' : 'Open booking'}
          </button>
        </form>
      </section>
    </PublicLayout>
  );
}
