import type { UtilitiesChargeBreakdownItem } from '@/types';

type Props = {
    breakdown: UtilitiesChargeBreakdownItem[];
    totalAmount: number;
};

const formatAmount = (value: number) => `${value.toFixed(2)} BYN`;

const formatConsumption = (value: number) => value.toFixed(3);

const formatTariff = (value: number) => value.toFixed(4);

export default function UtilitiesChargeBreakdown({
    breakdown,
    totalAmount,
}: Props) {
    return (
        <div className="space-y-1">
            <div className="font-medium">{formatAmount(totalAmount)}</div>
            <ul className="space-y-0.5 text-xs text-gray-500">
                {breakdown.map((item) => (
                    <li key={item.key}>
                        {item.label}: {formatConsumption(item.consumption)}{' '}
                        {item.unit} × {formatTariff(item.tariff)} ={' '}
                        {formatAmount(item.amount)}
                    </li>
                ))}
            </ul>
        </div>
    );
}
