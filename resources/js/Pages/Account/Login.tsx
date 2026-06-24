import PublicLayout from '@/Components/Layout/PublicLayout';
import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';

type PageProps = { errors?: Record<string, string> };

function inputClass() {
  return 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-slate-950';
}

export default function Login() {
  const { props } = usePage<PageProps>();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  function submit(event: FormEvent) {
    event.preventDefault();
    setSubmitting(true);
    router.post('/account/login', { email, password, remember }, {
      preserveState: true,
      onFinish: () => setSubmitting(false),
    });
  }

  return (
    <PublicLayout>
      <section className="mx-auto max-w-md px-4 py-12 sm:px-6 lg:px-8">
        <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <h1 className="text-2xl font-black">Customer login</h1>
          <p className="mt-2 text-sm text-slate-500">Sign in to view and manage your bookings.</p>
          <form onSubmit={submit} className="mt-6 grid gap-3">
            <input
              className={inputClass()}
              type="email"
              autoComplete="email"
              required
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              placeholder="Email"
            />
            {props.errors?.email && <p className="text-sm font-bold text-rose-600">{props.errors.email}</p>}
            <input
              className={inputClass()}
              type="password"
              autoComplete="current-password"
              required
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              placeholder="Password"
            />
            {props.errors?.password && <p className="text-sm font-bold text-rose-600">{props.errors.password}</p>}
            <label className="flex items-center gap-2 text-sm font-semibold text-slate-600">
              <input
                type="checkbox"
                checked={remember}
                onChange={(event) => setRemember(event.target.checked)}
              />
              Remember me
            </label>
            <button
              type="submit"
              disabled={submitting}
              className="rounded-2xl bg-slate-950 px-4 py-3 text-sm font-bold text-white disabled:opacity-60"
            >
              {submitting ? 'Signing in…' : 'Sign in'}
            </button>
          </form>
          <p className="mt-4 text-sm text-slate-500">
            New here?{' '}
            <Link href="/account/register" className="font-bold text-slate-950 hover:underline">
              Create an account
            </Link>
          </p>
        </div>
      </section>
    </PublicLayout>
  );
}
