import PublicLayout from '@/Components/Layout/PublicLayout';
import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';

type PageProps = { errors?: Record<string, string> };

function inputClass() {
  return 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-slate-950';
}

export default function Register() {
  const { props } = usePage<PageProps>();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [submitting, setSubmitting] = useState(false);

  function submit(event: FormEvent) {
    event.preventDefault();
    setSubmitting(true);
    router.post('/account/register', {
      name,
      email,
      phone,
      password,
      password_confirmation: passwordConfirmation,
    }, {
      preserveState: true,
      onFinish: () => setSubmitting(false),
    });
  }

  return (
    <PublicLayout>
      <section className="mx-auto max-w-md px-4 py-12 sm:px-6 lg:px-8">
        <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <h1 className="text-2xl font-black">Create account</h1>
          <p className="mt-2 text-sm text-slate-500">Register to book flights and track your trips.</p>
          <form onSubmit={submit} className="mt-6 grid gap-3">
            <input
              className={inputClass()}
              type="text"
              autoComplete="name"
              required
              value={name}
              onChange={(event) => setName(event.target.value)}
              placeholder="Full name"
            />
            {props.errors?.name && <p className="text-sm font-bold text-rose-600">{props.errors.name}</p>}
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
              type="tel"
              autoComplete="tel"
              value={phone}
              onChange={(event) => setPhone(event.target.value)}
              placeholder="Phone (optional)"
            />
            {props.errors?.phone && <p className="text-sm font-bold text-rose-600">{props.errors.phone}</p>}
            <input
              className={inputClass()}
              type="password"
              autoComplete="new-password"
              required
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              placeholder="Password"
            />
            {props.errors?.password && <p className="text-sm font-bold text-rose-600">{props.errors.password}</p>}
            <input
              className={inputClass()}
              type="password"
              autoComplete="new-password"
              required
              value={passwordConfirmation}
              onChange={(event) => setPasswordConfirmation(event.target.value)}
              placeholder="Confirm password"
            />
            <button
              type="submit"
              disabled={submitting}
              className="rounded-2xl bg-slate-950 px-4 py-3 text-sm font-bold text-white disabled:opacity-60"
            >
              {submitting ? 'Creating…' : 'Create account'}
            </button>
          </form>
          <p className="mt-4 text-sm text-slate-500">
            Already have an account?{' '}
            <Link href="/account/login" className="font-bold text-slate-950 hover:underline">
              Sign in
            </Link>
          </p>
        </div>
      </section>
    </PublicLayout>
  );
}
