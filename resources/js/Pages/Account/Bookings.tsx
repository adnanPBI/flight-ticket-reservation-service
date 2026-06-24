import PublicLayout from '@/Components/Layout/PublicLayout';
import { Link } from '@inertiajs/react';
import { formatMoney } from '@/Components/Flights/money';

type BookingRow = {
  reference: string;
  status: string;
  origin?: string | null;
  destination?: string | null;
  departure_at?: string | null;
  currency: string;
  total_amount_minor: number;
  pnr?: string | null;
  manage_url: string;
  created_at?: string | null;
};

export default function Bookings({ bookings = [], isAuthenticated = false }: { bookings: BookingRow[]; isAuthenticated: boolean }) {
  return (
    <PublicLayout>
      <section className="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
          <div>
            <p className="text-sm font-bold uppercase tracking-[0.25em] text-slate-500">Account</p>
            <h1 className="mt-2 text-3xl font-black">My bookings</h1>
            <p className="mt-2 text-slate-600">Logged-in customers can see their booking history here. Guests should use manage booking lookup.</p>
          </div>
          <Link href="/manage-booking" className="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white">
            Find guest booking
          </Link>
        </div>

        {!isAuthenticated && (
          <div className="mt-6 rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm font-semibold text-amber-800">
            You are not logged in. Use the guest booking lookup with booking reference and email, or add real auth in the next hardening phase.
          </div>
        )}

        <div className="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
          {bookings.length === 0 ? (
            <div className="p-10 text-center">
              <p className="text-lg font-black text-slate-950">No account-linked bookings yet.</p>
              <p className="mt-2 text-sm text-slate-500">Guest bookings can still be opened from the manage booking lookup page.</p>
            </div>
          ) : (
            <div className="divide-y divide-slate-100">
              {bookings.map((booking) => (
                <div key={booking.reference} className="grid gap-4 p-5 md:grid-cols-[1.3fr_1fr_1fr_auto] md:items-center">
                  <div>
                    <p className="text-xs font-bold uppercase text-slate-500">Reference</p>
                    <p className="font-black">{booking.reference}</p>
                    <p className="mt-1 text-sm text-slate-500">{booking.origin ?? '---'} → {booking.destination ?? '---'}</p>
                  </div>
                  <div>
                    <p className="text-xs font-bold uppercase text-slate-500">Status</p>
                    <p className="font-bold">{booking.status}</p>
                    <p className="mt-1 text-sm text-slate-500">PNR: {booking.pnr ?? 'Pending'}</p>
                  </div>
                  <div>
                    <p className="text-xs font-bold uppercase text-slate-500">Total</p>
                    <p className="font-black">{formatMoney(booking.total_amount_minor, booking.currency)}</p>
                    <p className="mt-1 text-sm text-slate-500">{booking.departure_at ? new Date(booking.departure_at).toLocaleString() : 'Date pending'}</p>
                  </div>
                  <Link href={booking.manage_url} className="rounded-2xl border border-slate-200 px-4 py-3 text-center text-sm font-black hover:bg-slate-50">
                    Manage
                  </Link>
                </div>
              ))}
            </div>
          )}
        </div>
      </section>
    </PublicLayout>
  );
}
