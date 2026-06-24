import PublicLayout from '@/Components/Layout/PublicLayout';
import FlightSearchForm, { type FlightSearchDefaults } from '@/Components/Flights/FlightSearchForm';

export default function Search({ defaults }: { defaults: FlightSearchDefaults }) {
  return (
    <PublicLayout>
      <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div className="mb-8">
          <h1 className="text-3xl font-black text-slate-950">Search flights</h1>
          <p className="mt-2 text-slate-600">Duffel mock mode works immediately. Add real credentials later and disable mock mode.</p>
        </div>
        <FlightSearchForm defaults={defaults} />
      </section>
    </PublicLayout>
  );
}
