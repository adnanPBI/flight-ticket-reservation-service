export function formatMoney(amountMinor: number, currency = 'BDT') {
  const amount = amountMinor / 100;

  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency,
    maximumFractionDigits: 0,
  }).format(amount);
}

export function formatDuration(minutes?: number | null) {
  if (!minutes) return 'Duration TBA';
  const hours = Math.floor(minutes / 60);
  const mins = minutes % 60;
  return `${hours}h ${mins}m`;
}

export function formatDateTime(value?: string | null) {
  if (!value) return 'Time TBA';
  return new Intl.DateTimeFormat('en-GB', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value));
}
