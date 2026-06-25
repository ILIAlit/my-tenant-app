import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import {
    DashboardSection,
    DashboardSectionScroll,
} from '@/components/dashboard/dashboard-section';
import payments from '@/routes/payments';
import type { PaymentListItem, PaymentStatus } from '@/types';
import { paymentStatusLabels } from '@/types';

type RecentPaymentsSectionProps = {
    recentPayments: PaymentListItem[];
    fitContent?: boolean;
    compact?: boolean;
};

const statusBadge: Record<PaymentStatus, string> = {
    pending: 'bg-amber-100 text-amber-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
};

const formatAmount = (value: number) => `${value.toFixed(2)} BYN`;

export default function RecentPaymentsSection({
    recentPayments,
    fitContent = false,
    compact = false,
}: RecentPaymentsSectionProps) {
    return (
        <DashboardSection
            title="Последние платежи"
            fitContent={fitContent}
            compact={compact}
            action={
                <Button
                    variant="outline"
                    size="sm"
                    className={cn(
                        'h-8 px-2 text-xs',
                        compact && 'h-6 px-1.5 text-[10px]',
                    )}
                    asChild
                >
                    <Link href={payments.get()}>Все</Link>
                </Button>
            }
        >
            {recentPayments.length === 0 ? (
                <p className="text-muted-foreground text-xs">
                    Платежей пока нет
                </p>
            ) : (
                <DashboardSectionScroll fitContent={fitContent}>
                    <div className={compact ? 'space-y-1' : 'space-y-2'}>
                        {recentPayments.map((payment) => (
                            <div
                                key={payment.id}
                                className={cn(
                                    'flex items-start justify-between gap-2 rounded-md border px-2',
                                    compact ? 'py-1' : 'py-1.5',
                                )}
                            >
                                <div className="min-w-0">
                                    <p className="truncate text-sm font-medium">
                                        {payment.renter.full_name}
                                    </p>
                                    <p className="text-muted-foreground text-[10px]">
                                        {payment.created_at}
                                    </p>
                                </div>
                                <div className="flex shrink-0 flex-col items-end gap-0.5">
                                    <p className="text-sm font-medium">
                                        {formatAmount(payment.amount)}
                                    </p>
                                    <span
                                        className={cn(
                                            'inline-block rounded-full px-2 py-0.5 text-[10px] font-medium',
                                            statusBadge[payment.status],
                                        )}
                                    >
                                        {paymentStatusLabels[payment.status]}
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                </DashboardSectionScroll>
            )}
        </DashboardSection>
    );
}
