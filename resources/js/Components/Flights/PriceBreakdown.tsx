import { formatMoney } from './money';

type Props = {
  currency: string;
  base_amount_minor?: number;
  tax_amount_minor?: number;
  fee_amount_minor?: number;
  markup_amount_minor?: number;
  discount_amount_minor?: number;
  total_amount_minor: number;
};

export default function PriceBreakdown(props: Props) {
  const rows = [
    ['Base fare', props.base_amount_minor ?? 0],
    ['Taxes', props.tax_amount_minor ?? 0],
    ['Fees', props.fee_amount_minor ?? 0],
    ['Service markup', props.markup_amount_minor ?? 0],
    ['Discount', -(props.discount_amount_minor ?? 0)],
  ];

  return (
    <div className="rounded-3xl border border-slate-200 bg-white p-5">
      <h3 className="text-lg font-bold">Price breakdown</h3>
      <div className="mt-4 space-y-3 text-sm">
        {rows.map(([label, amount]) => (
          <div key={label} className="flex items-center justify-between text-slate-600">
            <span>{label}</span>
            <span>{formatMoney(Number(amount), props.currency)}</span>
          </div>
        ))}
        <div className="border-t border-slate-200 pt-3 font-black text-slate-950">
          <div className="flex items-center justify-between">
            <span>Total</span>
            <span>{formatMoney(props.total_amount_minor, props.currency)}</span>
          </div>
        </div>
      </div>
    </div>
  );
}
