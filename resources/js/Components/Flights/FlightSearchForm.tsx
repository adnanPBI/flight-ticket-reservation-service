import { useForm } from '@inertiajs/react';

export type FlightSearchDefaults = {
  origin: string;
  destination: string;
  departure_date: string;
  return_date?: string | null;
  trip_type: 'one_way' | 'round_trip';
  adult_count: number;
  child_count: number;
  infant_count: number;
  cabin_class: 'economy' | 'premium_economy' | 'business' | 'first';
  currency: string;
  provider: 'duffel' | 'amadeus';
};

const inputClass = 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none ring-0 transition focus:border-slate-400';
const labelClass = 'text-xs font-semibold uppercase tracking-wide text-slate-500';

export default function FlightSearchForm({ defaults }: { defaults: FlightSearchDefaults }) {
  const { data, setData, post, processing, errors } = useForm<FlightSearchDefaults>(defaults);

  function submit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    post('/flights/search');
  }

  return (
    <form onSubmit={submit} className="rounded-[2rem] bg-white p-4 shadow-xl shadow-slate-200/70 ring-1 ring-slate-200 sm:p-6">
      <div className="grid gap-4 md:grid-cols-4">
        <label className="space-y-2">
          <span className={labelClass}>From</span>
          <input className={inputClass} value={data.origin} maxLength={3} onChange={(e) => setData('origin', e.target.value.toUpperCase())} placeholder="DAC" />
          {errors.origin && <p className="text-xs text-red-600">{errors.origin}</p>}
        </label>

        <label className="space-y-2">
          <span className={labelClass}>To</span>
          <input className={inputClass} value={data.destination} maxLength={3} onChange={(e) => setData('destination', e.target.value.toUpperCase())} placeholder="CXB" />
          {errors.destination && <p className="text-xs text-red-600">{errors.destination}</p>}
        </label>

        <label className="space-y-2">
          <span className={labelClass}>Departure</span>
          <input type="date" className={inputClass} value={data.departure_date} onChange={(e) => setData('departure_date', e.target.value)} />
          {errors.departure_date && <p className="text-xs text-red-600">{errors.departure_date}</p>}
        </label>

        <label className="space-y-2">
          <span className={labelClass}>Return</span>
          <input type="date" className={inputClass} value={data.return_date ?? ''} disabled={data.trip_type === 'one_way'} onChange={(e) => setData('return_date', e.target.value)} />
          {errors.return_date && <p className="text-xs text-red-600">{errors.return_date}</p>}
        </label>
      </div>

      <div className="mt-4 grid gap-4 md:grid-cols-6">
        <label className="space-y-2 md:col-span-2">
          <span className={labelClass}>Trip type</span>
          <select className={inputClass} value={data.trip_type} onChange={(e) => setData('trip_type', e.target.value as FlightSearchDefaults['trip_type'])}>
            <option value="one_way">One way</option>
            <option value="round_trip">Round trip</option>
          </select>
        </label>

        <label className="space-y-2">
          <span className={labelClass}>Adults</span>
          <input type="number" min={1} max={9} className={inputClass} value={data.adult_count} onChange={(e) => setData('adult_count', Number(e.target.value))} />
        </label>

        <label className="space-y-2">
          <span className={labelClass}>Children</span>
          <input type="number" min={0} max={8} className={inputClass} value={data.child_count} onChange={(e) => setData('child_count', Number(e.target.value))} />
        </label>

        <label className="space-y-2">
          <span className={labelClass}>Infants</span>
          <input type="number" min={0} max={4} className={inputClass} value={data.infant_count} onChange={(e) => setData('infant_count', Number(e.target.value))} />
        </label>

        <label className="space-y-2">
          <span className={labelClass}>Cabin</span>
          <select className={inputClass} value={data.cabin_class} onChange={(e) => setData('cabin_class', e.target.value as FlightSearchDefaults['cabin_class'])}>
            <option value="economy">Economy</option>
            <option value="premium_economy">Premium</option>
            <option value="business">Business</option>
            <option value="first">First</option>
          </select>
        </label>
      </div>

      <div className="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p className="text-sm text-slate-500">Mock mode returns usable demo offers until Duffel credentials are added.</p>
        <button disabled={processing} className="rounded-2xl bg-slate-950 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-slate-300 transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60">
          {processing ? 'Searching...' : 'Search flights'}
        </button>
      </div>
    </form>
  );
}
