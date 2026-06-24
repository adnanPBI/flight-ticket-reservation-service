import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type PassengerCounts = {
  adult: number;
  child: number;
  infant_without_seat: number;
};

export type PassengerInput = {
  passenger_type: 'adult' | 'child' | 'infant_without_seat';
  title: string;
  first_name: string;
  last_name: string;
  date_of_birth: string;
  gender: string;
  nationality: string;
  passport_number: string;
  passport_expiry_date: string;
};

type BookingPassenger = Partial<PassengerInput>;

type Props = {
  bookingReference: string;
  passengerCounts: PassengerCounts;
  customerEmail?: string | null;
  customerPhone?: string | null;
  existingPassengers?: BookingPassenger[];
};

function buildPassengers(counts: PassengerCounts, existingPassengers: BookingPassenger[] = []): PassengerInput[] {
  const types: PassengerInput['passenger_type'][] = [
    ...Array.from({ length: counts.adult }, () => 'adult' as const),
    ...Array.from({ length: counts.child }, () => 'child' as const),
    ...Array.from({ length: counts.infant_without_seat }, () => 'infant_without_seat' as const),
  ];

  return types.map((type, index) => ({
    passenger_type: type,
    title: existingPassengers[index]?.title ?? '',
    first_name: existingPassengers[index]?.first_name ?? '',
    last_name: existingPassengers[index]?.last_name ?? '',
    date_of_birth: existingPassengers[index]?.date_of_birth ?? '',
    gender: existingPassengers[index]?.gender ?? '',
    nationality: existingPassengers[index]?.nationality ?? '',
    passport_number: existingPassengers[index]?.passport_number ?? '',
    passport_expiry_date: existingPassengers[index]?.passport_expiry_date ?? '',
  }));
}

function labelForType(type: PassengerInput['passenger_type']) {
  if (type === 'infant_without_seat') return 'Infant without seat';
  return type.charAt(0).toUpperCase() + type.slice(1);
}

export default function PassengerForm({ bookingReference, passengerCounts, customerEmail, customerPhone, existingPassengers = [] }: Props) {
  const { data, setData, post, processing, errors } = useForm({
    customer_email: customerEmail ?? '',
    customer_phone: customerPhone ?? '',
    passengers: buildPassengers(passengerCounts, existingPassengers),
  });

  const updatePassenger = (index: number, key: keyof PassengerInput, value: string) => {
    const passengers = [...data.passengers];
    passengers[index] = { ...passengers[index], [key]: value };
    setData('passengers', passengers);
  };

  const submit = (event: FormEvent) => {
    event.preventDefault();
    post(`/flights/bookings/${bookingReference}/passengers`, { preserveScroll: true });
  };

  return (
    <form onSubmit={submit} className="space-y-5">
      <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <h2 className="font-black text-slate-950">Contact details</h2>
        <p className="mt-1 text-sm text-slate-500">Used for booking confirmation and provider communication.</p>
        <div className="mt-4 grid gap-3 md:grid-cols-2">
          <div>
            <label className="text-xs font-bold uppercase text-slate-500">Email</label>
            <input
              value={data.customer_email}
              onChange={(event) => setData('customer_email', event.target.value)}
              className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
              placeholder="customer@example.com"
              type="email"
            />
            {errors.customer_email && <p className="mt-1 text-xs font-semibold text-red-600">{errors.customer_email}</p>}
          </div>
          <div>
            <label className="text-xs font-bold uppercase text-slate-500">Phone</label>
            <input
              value={data.customer_phone}
              onChange={(event) => setData('customer_phone', event.target.value)}
              className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
              placeholder="+8801XXXXXXXXX"
            />
            {errors.customer_phone && <p className="mt-1 text-xs font-semibold text-red-600">{errors.customer_phone}</p>}
          </div>
        </div>
      </div>

      {errors.booking && <div className="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">{errors.booking}</div>}
      {errors.passengers && <div className="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">{errors.passengers}</div>}

      {data.passengers.map((passenger, index) => (
        <div key={`${passenger.passenger_type}-${index}`} className="rounded-2xl border border-slate-200 bg-white p-4">
          <div className="flex items-center justify-between gap-3">
            <div>
              <p className="font-black text-slate-950">Passenger {index + 1}</p>
              <p className="text-sm text-slate-500">{labelForType(passenger.passenger_type)}</p>
            </div>
            <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{passenger.passenger_type}</span>
          </div>

          <div className="mt-4 grid gap-3 md:grid-cols-2">
            <div>
              <label className="text-xs font-bold uppercase text-slate-500">Title</label>
              <select
                value={passenger.title}
                onChange={(event) => updatePassenger(index, 'title', event.target.value)}
                className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
              >
                <option value="">Select</option>
                <option>Mr</option>
                <option>Mrs</option>
                <option>Ms</option>
                <option>Master</option>
                <option>Miss</option>
              </select>
            </div>
            <div>
              <label className="text-xs font-bold uppercase text-slate-500">Gender</label>
              <select
                value={passenger.gender}
                onChange={(event) => updatePassenger(index, 'gender', event.target.value)}
                className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
              >
                <option value="">Not specified</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
                <option value="unspecified">Unspecified</option>
              </select>
            </div>
            <div>
              <label className="text-xs font-bold uppercase text-slate-500">First name</label>
              <input
                value={passenger.first_name}
                onChange={(event) => updatePassenger(index, 'first_name', event.target.value)}
                className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                placeholder="As passport/NID"
              />
            </div>
            <div>
              <label className="text-xs font-bold uppercase text-slate-500">Last name</label>
              <input
                value={passenger.last_name}
                onChange={(event) => updatePassenger(index, 'last_name', event.target.value)}
                className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                placeholder="As passport/NID"
              />
            </div>
            <div>
              <label className="text-xs font-bold uppercase text-slate-500">Date of birth</label>
              <input
                value={passenger.date_of_birth}
                onChange={(event) => updatePassenger(index, 'date_of_birth', event.target.value)}
                className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                type="date"
              />
            </div>
            <div>
              <label className="text-xs font-bold uppercase text-slate-500">Nationality ISO-2</label>
              <input
                value={passenger.nationality}
                onChange={(event) => updatePassenger(index, 'nationality', event.target.value.toUpperCase())}
                className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm uppercase"
                placeholder="BD"
                maxLength={2}
              />
            </div>
            <div>
              <label className="text-xs font-bold uppercase text-slate-500">Passport number</label>
              <input
                value={passenger.passport_number}
                onChange={(event) => updatePassenger(index, 'passport_number', event.target.value)}
                className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                placeholder="Required for many international routes"
              />
            </div>
            <div>
              <label className="text-xs font-bold uppercase text-slate-500">Passport expiry</label>
              <input
                value={passenger.passport_expiry_date}
                onChange={(event) => updatePassenger(index, 'passport_expiry_date', event.target.value)}
                className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                type="date"
              />
            </div>
          </div>
        </div>
      ))}

      <button disabled={processing} className="w-full rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60">
        {processing ? 'Saving passenger details...' : 'Save passenger details and continue'}
      </button>
    </form>
  );
}
