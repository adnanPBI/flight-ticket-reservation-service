const steps = ['Fare selected', 'Passenger details', 'Payment', 'Provider booking', 'Confirmation'];

export default function BookingTimeline({ current = 1 }: { current?: number }) {
  return (
    <ol className="grid gap-3 rounded-3xl border border-slate-200 bg-white p-4 text-sm md:grid-cols-5">
      {steps.map((step, index) => (
        <li key={step} className={`rounded-2xl px-3 py-2 ${index <= current ? 'bg-slate-950 text-white' : 'bg-slate-100 text-slate-500'}`}>
          <span className="font-bold">{index + 1}.</span> {step}
        </li>
      ))}
    </ol>
  );
}
