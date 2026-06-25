import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    DashboardSection,
    DashboardSectionScroll,
} from '@/components/dashboard/dashboard-section';
import charges from '@/routes/charges';
import renters from '@/routes/renters';
import type { DashboardRenterWithDebt } from '@/types';
import { roomNumberLabel } from '@/types';

type RentersWithDebtSectionProps = {
    rentersWithDebt: DashboardRenterWithDebt[];
    compact?: boolean;
};

const formatAmount = (value: number) => `${value.toFixed(2)} BYN`;

const formatRoom = (renter: DashboardRenterWithDebt): string => {
    if (!renter.room) {
        return '—';
    }

    return roomNumberLabel(renter.room);
};

export default function RentersWithDebtSection({
    rentersWithDebt,
    compact = false,
}: RentersWithDebtSectionProps) {
    return (
        <DashboardSection
            title="Должники"
            description={compact ? undefined : 'Просроченные начисления'}
            fitContent={compact}
            action={
                <Button
                    variant="link"
                    size="sm"
                    className="h-auto px-0 text-xs"
                    asChild
                >
                    <Link href={charges.get()}>Все</Link>
                </Button>
            }
        >
            {rentersWithDebt.length === 0 ? (
                <p className="text-muted-foreground text-sm">
                    Арендаторов с просроченным долгом нет
                </p>
            ) : (
                <DashboardSectionScroll fitContent={compact}>
                    <div className="space-y-1.5">
                        {rentersWithDebt.map((renter) => (
                            <div
                                key={renter.id}
                                className="flex items-center justify-between gap-2 rounded-md border border-orange-200 bg-orange-50/40 px-2 py-1.5"
                            >
                                <div className="min-w-0">
                                    <Link
                                        href={renters.settings(renter.id)}
                                        className="truncate text-xs font-medium hover:underline sm:text-sm"
                                    >
                                        {renter.full_name}
                                    </Link>
                                    <p className="text-muted-foreground text-[10px]">
                                        {formatRoom(renter)}
                                    </p>
                                </div>
                                <p className="shrink-0 text-xs font-semibold text-orange-700 sm:text-sm">
                                    {formatAmount(renter.debt_amount)}
                                </p>
                            </div>
                        ))}
                    </div>
                </DashboardSectionScroll>
            )}
        </DashboardSection>
    );
}
