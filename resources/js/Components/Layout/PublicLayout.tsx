import { Link, router, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import ChatWidget from '@/Components/Support/ChatWidget';

type AuthUser = { name: string; email: string };
type PageProps = { auth?: { user?: AuthUser | null } };
type PublicLayoutProps = PropsWithChildren;

export default function PublicLayout({ children }: PublicLayoutProps) {
  const { props } = usePage<PageProps>();
  const user = props.auth?.user ?? null;

  return (
    <div className="min-h-screen bg-slate-50 text-slate-950">
      <header className="border-b border-slate-200 bg-white/90 backdrop-blur">
        <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
          <Link href="/" className="text-xl font-bold tracking-tight text-slate-950">
            SkyBridge OTA
          </Link>
          <nav className="flex items-center gap-4 text-sm font-medium text-slate-600">
            <Link href="/flights/search" className="hover:text-slate-950">Flights</Link>
            <Link href="/account/bookings" className="hover:text-slate-950">My bookings</Link>
            <Link href="/manage-booking" className="hover:text-slate-950">Manage</Link>
            <a href="#support" className="hover:text-slate-950">Support</a>
            {user ? (
              <span className="flex items-center gap-3">
                <span className="font-bold text-slate-950">Hi, {user.name}</span>
                <button
                  type="button"
                  onClick={() => router.post('/account/logout')}
                  className="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-950 hover:bg-slate-50"
                >
                  Logout
                </button>
              </span>
            ) : (
              <>
                <Link href="/account/login" className="hover:text-slate-950">Login</Link>
                <Link href="/account/register" className="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-bold text-white hover:bg-slate-800">
                  Register
                </Link>
              </>
            )}
          </nav>
        </div>
      </header>

      <main>{children}</main>

      <footer className="border-t border-slate-200 bg-white">
        <div className="mx-auto grid max-w-7xl gap-4 px-4 py-8 text-sm text-slate-500 sm:px-6 lg:px-8 md:grid-cols-3">
          <p>Laravel + Inertia React OTA foundation.</p>
          <p>Server-side pricing, promo codes, and safe payment snapshots.</p>
          <p>Reverb-ready support chat with Filament inbox.</p>
        </div>
      </footer>

      <ChatWidget />
    </div>
  );
}
